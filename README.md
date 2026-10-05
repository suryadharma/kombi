# Kombi - Komisi Bimbingan Skripsi

Aplikasi pengelolaan komisi bimbingan skripsi berbasis PHP native (MVC sederhana). Sistem ini digunakan untuk mengelola proses skripsi dari pengajuan judul hingga penilaian akhir.

## Fitur Utama

- Role-based access control (Superadmin, Kombi, Dosen Pembimbing, Dosen Penguji, Mahasiswa, Penguji Eksternal)
- Pengelolaan pengajuan judul, penunjukan pembimbing & penguji
- Penilaian Seminar Proposal, Seminar Hasil, Pra-Ujian, dan Ujian Skripsi
- Blind grading (mahasiswa tidak melihat nilai)
- Audit log untuk tindakan penting
- Sistem bypass nilai dengan audit trail

## Tech Stack

- PHP 8.2 + PDO MySQL
- MariaDB/MySQL
- Apache HTTP Server
- Bootstrap 5, DataTables
- Docker & Docker Compose (opsional)

## Struktur Direktori

- `src/` - Kode aplikasi (controllers, models, views, helpers, middleware, config)
- `scripts/` - Utilitas backup, restore & sinkronisasi
- `migrations/`, `docs/`, `plans/` - Dokumentasi dan migrasi database
- `storage/` - File backup dan storage lokal

## Instalasi

### Menggunakan Docker

```bash
cp .env.example .env
docker compose -f docker-compose.kombi.yml up -d
```

Akses aplikasi di `http://localhost:9201` (default).

### Manual

1. Clone repository
2. Salin `.env.example` ke `.env` dan sesuaikan konfigurasi database
3. Import database (`schema.sql`, `seed.sql`, atau `kbs_db.sql`)
4. Konfigurasi web server ke direktori `src/` atau root project sesuai kebutuhan
5. Pastikan PHP 8.2 dengan ekstensi `pdo_mysql` tersedia

## Konfigurasi

Sesuaikan variabel di `.env` untuk:
- Database (DB_HOST, DB_NAME, DB_USER, DB_PASS)
- Port aplikasi dan phpMyAdmin
- Timezone dan debug mode
- Path backup dan storage

## Database

File SQL yang tersedia:
- `schema.sql` - Struktur database
- `seed.sql` - Data awal
- `kbs_db.sql` - Database lengkap (contoh)

## Lisensi

Proprietary - Hak cipta milik tim pengembang.
