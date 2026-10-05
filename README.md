# Komisi Bimbingan Skripsi (KBS)

Aplikasi pengelolaan komisi bimbingan skripsi untuk Prodi Teknologi Industri Pertanian (TIP). Sistem dibangun dengan PHP native (MVC sederhana), MySQL/MariaDB, Apache, Bootstrap 5, dan siap dijalankan melalui Docker Compose atau instalasi manual.

## Ringkasan Fitur

- Role Based Access Control dengan peran Superadmin, Kombi, Dosen Pembimbing, Dosen Penguji, Mahasiswa, dan Penguji Eksternal.
- Pengelolaan pengajuan judul, verifikasi, penunjukan pembimbing/penguji, serta penilaian SemPro, SemHas, Pra-Ujian, dan Ujian Skripsi.
- Panel nilai live (hari H), bypass nilai dengan audit trail, dan laporan masa studi, SLA, distribusi dosen.
- Mahasiswa tidak pernah melihat angka nilai (blind grading).
- Proteksi keamanan: hashing bcrypt, CSRF token setiap form, validasi server-side, audit log aksi penting.

## Arsitektur & Dependensi

- PHP 8.2 dengan ekstensi `pdo_mysql`
- MariaDB 11 / MySQL 8
- Apache HTTP Server
- Bootstrap 5, DataTables, dan komponen UI statis di `src/public` dan `src/views`
- Docker & Docker Compose (opsional tetapi direkomendasikan)

Direktori penting:

- `src/` – kode aplikasi (controllers, models, views, helpers, middleware)
- `src/config/` – konfigurasi aplikasi, database, backup, dan routing
- `scripts/` – utilitas backup, restore, sinkronisasi
- `storage/` & `src/storage/` – arsip backup lokal dan log
- `schema.sql`, `seed.sql`, `kbs_db.sql` – skema dan data contoh database

## Prasyarat & Persiapan

1. **Docker** dan **Docker Compose** (jika menjalankan via container).
2. Untuk instalasi manual: PHP ≥ 8.2, ekstensi `pdo_mysql`, Apache/Nginx, dan MySQL/MariaDB.
3. Siapkan file konfigurasi lingkungan `.env` berdasarkan `.env.example`.

Salin contoh konfigurasi:

```bash
cp .env.example .env
```

Sesuaikan variabel berikut sebelum deployment:

- `APP_HTTP_PORT`, `PHPMYADMIN_HTTP_PORT` (port akses aplikasi & phpMyAdmin)
- `DB_*` (nama host, user, password, port, dan root password jika butuh)
- `APP_TIMEZONE`, `APP_DEBUG`
- `APP_SOURCE_PATH` dan `APP_BACKUP_MIRROR_PATH` bila sumber aplikasi/tujuan mirror berada di path custom
- Parameter backup (`BACKUP_*`) untuk sinkronisasi ke penyimpanan eksternal (lokal atau SMB)

> Docker Compose otomatis memuat `.env` pada direktori yang sama. Untuk Portainer, unggah `.env` bersama `docker-compose.kombi.yml` atau tambahkan variabel environment manual.

## Menjalankan dengan Docker Compose

```bash
docker compose -f docker-compose.kombi.yml up -d
```

Layanan yang tersedia:

- Aplikasi utama: `http://HOST:APP_HTTP_PORT` (default `9201`)
- phpMyAdmin: `http://HOST:PHPMYADMIN_HTTP_PORT` (default `9202`)

Volume/persistent storage:

- Data database: volume named `kbs_dbdata`
- Kode aplikasi: bind mount ke `${APP_SOURCE_PATH}` (default direktori project)
- Mirror backup lokal: `${APP_BACKUP_MIRROR_PATH}` (default `./storage/backups_mirror`)

### Deploy via Portainer

1. Buka Portainer → menu **Stacks** → **Add Stack**.
2. Unggah `docker-compose.kombi.yml` dan `.env`, atau paste konten Compose.
3. Sesuaikan variabel environment di UI Portainer bila diperlukan.
4. Klik **Deploy the stack**.

## Migrasi ke Server Baru

1. **Salin kode sumber** (seluruh folder project) ke server baru.
2. **Backup database & uploads** dari server lama:
   - Gunakan `scripts/backup-kombi.sh` (membuat arsip `.tar.gz` yang berisi dump SQL dan folder uploads), atau
   - Jalankan `php artisan`? (tidak ada) – alternatif: `mysqldump` manual dan rsync uploads.
3. **Restore di server baru**:
   - Jalankan `docker compose up -d` (atau setup manual PHP+MySQL).
   - Gunakan `scripts/restore-kombi.sh <arsip.tar.gz>` untuk mengembalikan database & uploads.
4. **Perbarui `.env`** dengan nilai spesifik server baru (hostname, kredensial DB, konfigurasi backup/SMB, path mirror).
5. **Tes end-to-end**: login setiap role, lakukan pengajuan judul dummy, cek panel dosen/penguji, jalankan laporan.
6. **Putuskan cadangan SMB/lokal** sesuai infra baru (atur variabel `BACKUP_EXTERNAL_TYPE` dan lainnya).

> Semua path default relatif terhadap direktori project, jadi aplikasi dapat dipindahkan hanya dengan menyalin folder dan menyesuaikan `.env`.

## Konfigurasi Backup

Diatur melalui `src/config/backup.php` dan variabel `.env`:

- `BACKUP_STORAGE_PATH` – override lokasi backup lokal (default `src/storage/backups`).
- `BACKUP_EXTERNAL_TYPE`
  - `path`: mirror ke folder lokal lain (`BACKUP_EXTERNAL_PATH`).
  - `smb`: sinkronisasi ke SMB share (`BACKUP_SMB_*`).
- `BACKUP_RETENTION_DAYS` – retensi arsip.

Gunakan skrip:

- `scripts/backup-kombi.sh` – membuat backup penuh dan mirror (memerlukan Docker).
- `scripts/restore-kombi.sh <arsip>` – mengembalikan backup.
- `scripts/sync_backups_to_smb.php` – sinkronisasi ulang ke SMB bila diperlukan.

## Kredensial Default

| Username     | Password | Peran                |
|--------------|----------|----------------------|
| superadmin   | password | Superadmin           |
| kombi        | password | Kombi                |
| pembimbing1  | password | Dosen Pembimbing     |
| pembimbing2  | password | Dosen Pembimbing     |
| penguji1     | password | Dosen Penguji        |
| penguji2     | password | Dosen Penguji        |
| mahasiswa1   | password | Mahasiswa            |
| mahasiswa2   | password | Mahasiswa            |
| mahasiswa3   | password | Mahasiswa            |

Segera ganti password setelah login pertama untuk keamanan produksi.

## Alur Penggunaan per Peran

- **Superadmin**: kelola akun, role, kebijakan global (rasio nilai, ambang cuti, kuota dosen).
- **Kombi**: verifikasi judul, tetapkan pembimbing/penguji, atur jadwal sidang, lakukan bypass nilai, kelola laporan & backup.
- **Dosen Pembimbing**: lihat mahasiswa bimbingan, input nilai Sempro/Semhas/Pra-Ujian.
- **Dosen Penguji**: akses panel live hari H, input nilai Sempro/Semhas/Ujian Skripsi, melihat nilai pembimbing sesuai aturan waktu.
- **Mahasiswa**: ajukan judul, unggah dokumen, meninjau timeline tanpa nilai.
- **Penguji Eksternal**: akses panel live via NIP + token (atau akun sementara), wajib menyetujui NDA ringkas.

## Database & Seed

- `schema.sql` – struktur tabel.
- `seed.sql` – data awal untuk peran dan konfigurasi dasar.
- `kbs_db.sql` – gabungan skema + data contoh.

Import manual:

```bash
mysql -u <user> -p<password> <database> < schema.sql
mysql -u <user> -p<password> <database> < seed.sql
```

Atau gunakan `scripts/restore-kombi.sh` dengan arsip hasil backup.

## Pemeliharaan & Pengujian

- Jalankan backup berkala (bisa dijadwalkan cron `scripts/backup-kombi.sh`).
- Audit log tersimpan di tabel database dan dapat diekspor dari UI.
- Unit test sederhana berada di `src/tests` (bila tersedia) – jalankan via PHPUnit setelah menyesuaikan environment.
- Pantau direktori `src/storage/logs` untuk error aplikasi.

## Catatan Keamanan

- Password tersimpan menggunakan `password_hash` (bcrypt).
- Token CSRF wajib pada semua form; pastikan `session.save_path` berjalan di server baru.
- Set `APP_DEBUG=0` pada lingkungan produksi untuk menonaktifkan tampilan error.
- Batasi akses terhadap folder `storage/` dan `scripts/` jika di-host di shared hosting.

## Referensi Tambahan

- `Dockerfile` – build image PHP 8.2 + Apache dengan konfigurasi rewrite.
- `req.txt` – prompt teknis awal (historis).
- `CODex-environment-notes.txt` – catatan koneksi lingkungan sebelumnya.

Silakan gunakan issue tracker internal atau catatan terpisah untuk perubahan bisnis atau bug report terbaru. Hubungi tim Kombi-TIP bila membutuhkan bantuan lanjutan terkait deployment atau pengembangan fitur baru.
