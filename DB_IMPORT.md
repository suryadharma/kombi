# Database Import Guide

Database hasil dump dari server produksi (kbs_db) telah disimpan di file ini.

## File
`kbs_db_export_20261005.sql` - Dump database MariaDB 11.4 dari container `kbs_db`

## Cara Import

### Via Docker (phpMyAdmin)
1. Buka phpMyAdmin (http://localhost:9202)
2. Pilih database `kbs_db`
3. Tab Import → Pilih file SQL → Execute

### Via Docker CLI
```bash
docker exec -i kbs_db mariadb -u root -prootpass123 kbs_db < kbs_db_export_20261005.sql
```

### Via Local MariaDB/MySQL
```bash
mariadb -u root -p kbs_db < kbs_db_export_20261005.sql
```

## Catatan
- Dump diambil dari server: root@100.64.194.39 (container kbs_db)
- Database name: kbs_db, user: kbs_user, password: kbs_pass
