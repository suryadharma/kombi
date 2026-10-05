#!/usr/bin/env bash

set -euo pipefail

# ------------------------------------------------------------------------------
# KomBi restore helper
# ------------------------------------------------------------------------------
# Restores a backup archive created by backup-kombi.sh
# Usage: restore-kombi.sh <archive.tar.gz>
# ------------------------------------------------------------------------------

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <archive.tar.gz> [--skip-uploads]" >&2
  exit 1
fi

ARCHIVE_PATH="$1"
SHIFT_ARGS=1
RESTORE_UPLOADS=true

if [[ $# -ge 2 && "$2" == "--skip-uploads" ]]; then
  RESTORE_UPLOADS=false
  SHIFT_ARGS=2
fi

if [[ ! -f "${ARCHIVE_PATH}" ]]; then
  echo "Error: archive not found at ${ARCHIVE_PATH}" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd -P)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd -P)"

COMPOSE_FILE="${COMPOSE_FILE:-${PROJECT_ROOT}/docker-compose.kombi.yml}"
DB_SERVICE="${DB_SERVICE:-kbs_db}"
WEB_ROOT="${WEB_ROOT:-${PROJECT_ROOT}/src/public/uploads}"

DB_HOST="${DB_HOST:-kbs_db}"
DB_NAME="${DB_NAME:-kbs_db}"
DB_USER="${DB_USER:-kbs_user}"
DB_PASS="${DB_PASS:-kbs_pass}"

if ! command -v docker >/dev/null 2>&1; then
  echo "Error: docker is required but not installed or not in PATH." >&2
  exit 1
fi

tmp_dir="$(mktemp -d)"
trap 'rm -rf "${tmp_dir}"' EXIT

echo "[*] Extracting archive..."
tar -xzf "${ARCHIVE_PATH}" -C "${tmp_dir}"

sql_file="${tmp_dir}/database.sql"
uploads_dir="${tmp_dir}/uploads"

if [[ ! -f "${sql_file}" ]]; then
  echo "Error: database.sql not found inside archive." >&2
  exit 1
fi

echo "[*] Restoring database '${DB_NAME}'..."
docker compose -f "${COMPOSE_FILE}" exec -T -e MYSQL_PWD="${DB_PASS}" "${DB_SERVICE}" \
  mariadb --ssl=0 -h "${DB_HOST}" -u "${DB_USER}" "${DB_NAME}" < "${sql_file}"

if [[ "${RESTORE_UPLOADS}" == true ]]; then
  if [[ -d "${uploads_dir}" ]]; then
    echo "[*] Restoring uploads to ${WEB_ROOT} ..."
    mkdir -p "${WEB_ROOT}"
    rsync -a "${uploads_dir}/" "${WEB_ROOT}/"
  else
    echo "[!] Warning: uploads directory missing in archive; skipping." >&2
  fi
fi

echo "[✓] Restore completed successfully."


