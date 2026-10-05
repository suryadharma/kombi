#!/usr/bin/env bash

set -euo pipefail

# ------------------------------------------------------------------------------
# KomBi backup helper
# ------------------------------------------------------------------------------
# This script creates an application backup consisting of:
#   - MySQL dump (logical backup)
#   - Public uploads directory
# The result is a compressed tarball stored locally and optionally copied
# to an external/SMB destination.
# ------------------------------------------------------------------------------

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd -P)"

COMPOSE_FILE="${COMPOSE_FILE:-${PROJECT_ROOT}/docker-compose.kombi.yml}"
DB_SERVICE="${DB_SERVICE:-kbs_db}"
WEB_ROOT="${WEB_ROOT:-${PROJECT_ROOT}/src/public/uploads}"
BACKUP_ROOT="${BACKUP_ROOT:-${PROJECT_ROOT}/src/storage/backups}"
EXTERNAL_TARGET="${EXTERNAL_TARGET:-${PROJECT_ROOT}/storage/backups_mirror}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"

DB_HOST="${DB_HOST:-kbs_db}"
DB_NAME="${DB_NAME:-kbs_db}"
DB_USER="${DB_USER:-kbs_user}"
DB_PASS="${DB_PASS:-kbs_pass}"

timestamp="$(date +%Y%m%d-%H%M%S)"
backup_prefix="kombi-backup-${timestamp}"
tmp_dir="$(mktemp -d)"
trap 'rm -rf "${tmp_dir}"' EXIT

mkdir -p "${BACKUP_ROOT}"
[ -d "${WEB_ROOT}" ] || mkdir -p "${WEB_ROOT}"

sql_file="${tmp_dir}/database.sql"
uploads_dir="${tmp_dir}/uploads"
mkdir -p "${uploads_dir}"

echo "[*] Dumping database '${DB_NAME}' from service '${DB_SERVICE}'..."

if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is required but not installed or not in PATH." >&2
  exit 1
fi

if ! docker compose -f "${COMPOSE_FILE}" ps "${DB_SERVICE}" >/dev/null 2>&1; then
  echo "Error: cannot find service '${DB_SERVICE}' in compose file ${COMPOSE_FILE}." >&2
  exit 1
fi

# Use MYSQL_PWD to avoid leaking password in process list
if ! docker compose -f "${COMPOSE_FILE}" exec -T -e MYSQL_PWD="${DB_PASS}" "${DB_SERVICE}" \
  mariadb-dump --single-transaction --quick --lock-tables=false --ssl=0 \
  -h "${DB_HOST}" -u "${DB_USER}" "${DB_NAME}" > "${sql_file}"; then
  echo "Error: mysqldump failed." >&2
  exit 1
fi

echo "[*] Copying uploads directory..."
rsync -a --delete "${WEB_ROOT}/" "${uploads_dir}/" >/dev/null 2>&1 || true

archive_path="${BACKUP_ROOT}/${backup_prefix}.tar.gz"

echo "[*] Creating archive ${archive_path} ..."
tar -czf "${archive_path}" -C "${tmp_dir}" .

chmod 0640 "${archive_path}"

if [[ -n "${EXTERNAL_TARGET}" ]]; then
  mkdir -p "${EXTERNAL_TARGET}"
  if cp "${archive_path}" "${EXTERNAL_TARGET}/"; then
    echo "[*] Copied archive to ${EXTERNAL_TARGET}"
  else
    echo "[!] Warning: failed to copy archive to ${EXTERNAL_TARGET}" >&2
  fi
fi

if [[ "${RETENTION_DAYS}" =~ ^[0-9]+$ ]]; then
  echo "[*] Cleaning archives older than ${RETENTION_DAYS} days in ${BACKUP_ROOT}"
  find "${BACKUP_ROOT}" -maxdepth 1 -type f -name 'kombi-backup-*.tar.gz' -mtime +"${RETENTION_DAYS}" -print -delete 2>/dev/null || true
  if [ -n "${EXTERNAL_TARGET}" ] && [ -d "${EXTERNAL_TARGET}" ]; then
    find "${EXTERNAL_TARGET}" -maxdepth 1 -type f -name 'kombi-backup-*.tar.gz' -mtime +"${RETENTION_DAYS}" -print -delete 2>/dev/null || true
  fi
fi

echo "[✓] Backup completed: ${archive_path}"




