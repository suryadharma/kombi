# Fitur Email Notifikasi - Dokumentasi Implementasi

## 📋 Overview

Fitur ini menambahkan kemampuan untuk mengirim email otomatis berisi PDF hasil penilaian ke dosen pembimbing dan penguji setelah evaluasi selesai (SEMPRO, SEMHAS, PRA_UJIAN, UJIAN_SKRIPSI).

## 🗂️ File yang Ditambahkan/Dimodifikasi

### File Baru
1. **`migrations/add_email_feature.sql`** - Database migration untuk fitur email
2. **`src/services/EmailService.php`** - Service untuk handle pengiriman email via PHPMailer
3. **`src/helpers/EmailQueueHelper.php`** - Helper untuk manage email queue
4. **`src/cron/process_email_queue.php`** - Cron job untuk memproses email queue
5. **`src/libs/phpmailer/`** - PHPMailer library (v6.9.1)

### File yang Dimodifikasi
1. **`src/helpers/EventService.php`** - Menambahkan logic queue email saat event selesai
2. **`src/controllers/SettingsController.php`** - Menambahkan handle email settings
3. **`src/views/settings/index.php`** - Menambahkan form konfigurasi email
4. **`src/controllers/LecturerController.php`** - Menambahkan handle email field
5. **`src/views/lecturers/create.php`** - Menambahkan input email
6. **`src/views/lecturers/edit.php`** - Menambahkan input email

## 🔧 Setup

### 1. Jalankan Migration

```bash
mysql -u root -p kbs_db < migrations/add_email_feature.sql
```

### 2. Buat App Password Gmail

1. Buka https://myaccount.google.com/security
2. Aktifkan **2-Step Verification**
3. Go to **App Passwords**
4. Create app password untuk "Mail"
5. Copy password (16 karakter)

### 3. Konfigurasi Email

1. Login sebagai Superadmin/Kombi
2. Buka menu **Settings**
3. Scroll ke bagian **Konfigurasi Email**
4. Isi konfigurasi:
   - ✅ Aktifkan Fitur Email
   - SMTP Host: `smtp.gmail.com`
   - SMTP Port: `587` (TLS)
   - Email Pengirim: `email@gmail.com`
   - App Password: `xxxx xxxx xxxx xxxx`
   - Nama Pengirim: `Sistem Kombi - Fakultas`
   - Batch Size: `10` (jumlah email per batch)

### 4. Setup Cron Job

Tambahkan cron job untuk memproses email queue setiap 5 menit:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
*/5 * * * * cd /home/suryadharma/Workspaces/htdocs/kombi && php src/cron/process_email_queue.php >> logs/email_queue.log 2>&1
```

### 5. Update Data Dosen dengan Email

```sql
-- Contoh update email dosen
UPDATE lecturers SET email = 'dosen1@university.ac.id' WHERE nip = '19800101...';
UPDATE lecturers SET email = 'dosen2@university.ac.id' WHERE nip = '19850202...';
```

Atau melalui UI:
1. Buka menu **Dosen**
2. Klik **Edit** pada dosen yang ingin diupdate
3. Isi field **Email**
4. Klik **Update**

## 📊 Database Schema

### Tabel Baru

#### `email_queue`
Queue untuk pengiriman email asynchronous:
- `id` - Primary key
- `to_email` - Email penerima
- `to_name` - Nama penerima
- `subject` - Subject email
- `body` - Body email (HTML)
- `attachment_path` - Path ke PDF attachment
- `event_type` - Tipe event (SEMPRO, SEMHAS, PRA_UJIAN, UJIAN_SKRIPSI)
- `student_id` - ID mahasiswa
- `priority` - Priority (0=normal, 1=high, 2=urgent)
- `max_attempts` - Max retry attempts
- `attempts` - Current retry count
- `status` - Status (PENDING, PROCESSING, SENT, FAILED)
- `error_message` - Error message jika gagal
- `scheduled_at` - Waktu schedule
- `sent_at` - Waktu terkirim
- `created_at` - Waktu dibuat

#### `email_logs`
Log history pengiriman email:
- `id` - Primary key
- `recipient_email` - Email penerima
- `recipient_name` - Nama penerima
- `subject` - Subject email
- `event_type` - Tipe event
- `student_id` - ID mahasiswa
- `student_name` - Nama mahasiswa
- `pdf_path` - Path ke PDF
- `status` - Status (PENDING, SENT, FAILED, RETRY)
- `error_message` - Error message
- `attempts` - Jumlah percobaan
- `sent_at` - Waktu terkirim
- `created_at` - Waktu dibuat

### Kolom Baru

#### `lecturers.email`
- Type: `VARCHAR(150) NULL`
- Index: `idx_email`
- Deskripsi: Email dosen untuk notifikasi

#### `users.email`
- Type: `VARCHAR(150) NULL`
- Index: `idx_email`
- Deskripsi: Email user (untuk notifikasi masa depan)

### Settings Baru

| Key | Default | Deskripsi |
|-----|---------|-----------|
| `email_enabled` | `0` | Aktifkan fitur email |
| `email_host` | `smtp.gmail.com` | Host SMTP |
| `email_port` | `587` | Port SMTP |
| `email_username` | `` | Email pengirim |
| `email_password` | `` | App password |
| `email_from_name` | `Sistem Kombi` | Nama pengirim |
| `email_encryption` | `tls` | Enkripsi (tls/ssl) |
| `email_batch_size` | `10` | Batch size |
| `email_retry_delay` | `5` | Retry delay (menit) |
| `email_max_queue_age` | `24` | Max queue age (jam) |
| `email_cc_enabled` | `0` | CC ke mahasiswa |
| `email_send_copy_to_admin` | `0` | Copy ke admin |

## 🔄 Alur Kerja

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Dosen/Kombi mengisi nilai penilaian                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Sistem simpan nilai ke database                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. EventService::markEventCompletedIfScoresReady()          │
│    - Cek apakah semua dosen sudah mengisi nilai             │
│    - Jika ya, update event status = 'SELESAI'               │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. EventService::queueEvaluationEmails()                    │
│    - Generate PDF (TODO: integrate dengan PDF exporter)     │
│    - Queue email ke email_queue untuk setiap dosen          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. Cron Job (setiap 5 menit)                                │
│    - Ambil batch dari email_queue                           │
│    - Kirim email via PHPMailer                              │
│    - Update status queue                                    │
│    - Log ke email_logs                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. Dosen menerima email dengan PDF attachment               │
└─────────────────────────────────────────────────────────────┘
```

## 📧 Template Email

### Email ke Dosen

Subject: `Dokumen Penilaian [TIPE] - [NAMA MAHASISWA]`

Body:
- Header dengan judul event
- Informasi mahasiswa (Nama, NIM, Judul, Tanggal)
- Notifikasi PDF attachment
- Footer dengan info sistem

### Email ke Mahasiswa (CC)

Subject: `[CC] Dokumen Penilaian [TIPE] - [NAMA MAHASISWA]`

Body:
- Header dengan judul event
- Informasi mahasiswa
- Notifikasi bahwa PDF telah dikirim ke dosen
- Footer dengan info sistem

## ⚙️ Konfigurasi Tambahan

### Rate Limiting Gmail

- Gmail free tier: ~500 emails/hari
- Gunakan batch size yang kecil (10-20)
- Delay antar batch: 0.5 detik
- Retry dengan exponential backoff

### Monitoring

Cek status queue:
```sql
SELECT status, COUNT(*) as count FROM email_queue GROUP BY status;
```

Lihat log pengiriman:
```sql
SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 50;
```

### Troubleshooting

1. **Email tidak terkirim**
   - Cek log: `tail -f logs/email_queue.log`
   - Cek queue status di settings
   - Pastikan cron job berjalan

2. **Gmail authentication error**
   - Pastikan 2-Step Verification aktif
   - Gunakan App Password, bukan password akun
   - Cek jika App Password sudah di-revoke

3. **PDF tidak terlampir**
   - PDF generation belum diimplementasikan di EventService
   - Perlu integrate dengan PdfExporter

## 🚀 Pengembangan Lanjutan

### TODO

1. **Integrate PDF Generation**
   - Hubungkan `EventService::generateEvaluationPdf()` dengan `PdfExporter`
   - Generate PDF sebelum queue email

2. **Email Preview**
   - Tambah preview email sebelum kirim
   - Show di timeline mahasiswa

3. **Resend Email**
   - Fitur untuk resend email jika gagal
   - Manual trigger dari admin panel

4. **Email Template Management**
   - Edit template email dari admin panel
   - Multi-language support

5. **Third-party Email Service**
   - Integrasi dengan SendGrid/Mailgun
   - Untuk volume yang lebih tinggi

## 📞 Support

Untuk pertanyaan atau masalah, hubungi admin sistem.
