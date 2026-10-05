# Dokumentasi Alur Penggunaan Aplikasi KBS (Komisi Bimbingan Skripsi)

## 📋 Daftar Isi
1. [Overview Aplikasi](#overview-aplikasi)
2. [Pengguna dan Peran](#pengguna-dan-peran)
3. [Alur Utama Aplikasi](#alur-utama-aplikasi)
4. [Fitur per Role](#fitur-per-role)
5. [Flowchart Lengkap](#flowchart-lengkap)

---

## Overview Aplikasi

**KBS (Komisi Bimbingan Skripsi)** adalah sistem pengelolaan komisi bimbingan skripsi untuk Prodi Teknologi Industri Pertanian (TIP). Aplikasi ini mengelola seluruh siklus skripsi dari pengajuan judul hingga ujian skripsi.

### Tahapan Skripsi yang Dikelola:
1. **Seminar Proposal (SemPro)** - Presentasi proposal skripsi
2. **Seminar Hasil (SemHas)** - Presentasi hasil penelitian
3. **Pra-Ujian** - Pra-uji sebelum ujian skripsi
4. **Ujian Skripsi** - Ujian skripsi akhir

---

## Pengguna dan Peran

| Role | Kode Role | Deskripsi |
|------|-----------|-----------|
| **Superadmin** | `superadmin` | Administrator sistem dengan akses penuh |
| **Kombi** | `kombi` | Anggota Komisi Bimbingan yang mengelola verifikasi dan penugasan |
| **Dosen Pembimbing** | `dosen_pembimbing` | Dosen pembimbing skripsi mahasiswa |
| **Dosen Penguji** | `dosen_penguji` | Dosen penguji ujian skripsi |
| **Mahasiswa** | `mahasiswa` | Mahasiswa yang menyelesaikan skripsi |
| **Penguji Eksternal** | `penguji_eksternal` | Penguji dari luar kampus |

---

## Alur Utama Aplikasi

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           ALUR UTAMA SIKLUS SKRIPSI                             │
└─────────────────────────────────────────────────────────────────────────────────┘

    MAHASISWA                    KOMBI                   DOSEN                 SUPERADMIN
       │                           │                       │                        │
       │  1. Login                 │                       │                        │
       ├──────────────────────────>│                       │                        │
       │                           │                       │                        │
       │  2. Ajukan Judul          │                       │                        │
       ├──────────────────────────>│                       │                        │
       │                           │                       │                        │
       │                           │  3. Verifikasi Judul  │                        │
       │                           ├──────────────────────>│                        │
       │                           │<──────────────────────┤                        │
       │                           │                       │                        │
       │  4. Judul Disetujui       │                       │                        │
       │<──────────────────────────┤                       │                        │
       │                           │                       │                        │
       │                           │  5. Set Pembimbing    │                        │
       │                           │    & Penguji          │                        │
       │                           ├──────────────────────>│                        │
       │                           │                       │                        │
       │  6. Bimbingan             │<──────────────────────┤                        │
       │  (Proses Skripsi)         │                       │                        │
       │                           │                       │                        │
       │                           │  7. Jadwal SemPro     │                        │
       │                           ├──────────────────────>│                        │
       │                           │                       │                        │
       │  8. Ikuti SemPro          │                       │                        │
       ├──────────────────────────>│                       │                        │
       │                           │                       │                        │
       │                           │  9. Input Nilai       │                        │
       │                           │<──────────────────────┤                        │
       │                           │                       │                        │
       │                           │  10. Verifikasi Nilai │                        │
       │                           ├──────────────────────>│                        │
       │                           │<──────────────────────┤                        │
       │                           │                       │                        │
       │  11. SemHas → Pra-Ujian → Ujian (Ulangi langkah 7-10)                     │
       │                           │                       │                        │
       │  12. Lihat Timeline &     │                       │                        │
       │      Nilai (jika dibuka)  │                       │                        │
       │                           │                       │                        │
       │                           │  13. Laporan          │                        │
       │                           │  Kelulusan            │                        │
       │                           ├───────────────────────────────────────────────>│
       │                           │<───────────────────────────────────────────────┤
```

---

## Fitur per Role

### 1. MAHASISSA

**Dashboard:**
- Lihat timeline perjalanan skripsi
- Lihat status setiap tahap (Menunggu, Proses, Terjadwal, Selesai)
- Lihat nilai (jika diizinkan oleh Kombi)
- Blind grading: tidak melihat angka nilai

**Fitur yang Tersedia:**

| Menu | Deskripsi |
|------|-----------|
| `/titles/submit` | Mengajukan judul skripsi dengan usulan pembimbing & penguji |
| `/timeline` | Melihat timeline perjalanan skripsi dan status |
| `/timeline/scores/{stage}` | Melihat detail nilai per tahap (jika dibuka) |
| `/profile` | Mengubah profil dan password |

**Alur Penggunaan Mahasiswa:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                        ALUR PENGGUNAAN MAHASISWA                                │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │   LOGIN  │
    └─────┬────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                              DASHBOARD                                       │
    │  ┌───────────────────────────────────────────────────────────────────────┐  │
    │  │  Timeline Perjalanan Skripsi                                           │  │
    │  │  ┌──────────┐   ┌──────────┐   ┌──────────┐   ┌──────────┐            │  │
    │  │  │ Pengajuan│ → │ SemPro  │ → │ SemHas  │ → │  Ujian   │            │  │
    │  │  │  Judul   │   │         │   │         │   │          │            │  │
    │  │  └──────────┘   └──────────┘   └──────────┘   └──────────┘            │  │
    │  └───────────────────────────────────────────────────────────────────────┘  │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          ├─────────────────────────────────────────────────────────────────────────┐
          │                                                                         │
          v                                                                         v
    ┌─────────────────────┐                                               ┌─────────────────┐
    │  AJUKAN JUDUL       │                                               │  LIHAT TIMELINE │
    │  /titles/submit     │                                               │  /timeline      │
    └─────────┬───────────┘                                               └─────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  Form Pengajuan Judul                                                        │
    │  - Judul Skripsi                                                             │
    │  - Abstrk                                                                   │
    │  - Kata Kunci                                                               │
    │  - Usulan Pembimbing 1 & 2                                                  │
    │  - Usulan Penguji 1, 2, & 3                                                 │
    │  - Link Surat Tugas (untuk pembimbing/penguji yang sudah ditugaskan)        │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────┐
    │  TUNGGU VERIFIKASI  │
    │  dari Kombi         │
    └─────────┬───────────┘
              │
              v
    ┌─────────────────────┐     ┌─────────────────────┐
    │  JUDUL DITERIMA     │     │  JUDUL DITOLAK      │
    │  Lanjut Bimbingan   │     │  Ajukan Ulang       │
    └─────────────────────┘     └─────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                        PROSES BIMBINGAN SKRIPSI                              │
    │  - Konsultasi dengan Pembimbing                                             │
    │  - Persiapan materi untuk setiap seminar/ujian                              │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                        TAHAPAN SEMINAR & UJIAN                               │
    │                                                                             │
    │  1. SEMINAR PROPOSAL (SemPro)                                               │
    │     - Kombi membuat jadwal                                                   │
    │     - Mahasiswa presentasi proposal                                          │
    │     - Dosen input nilai                                                     │
    │     - Kombi verifikasi nilai                                                │
    │                                                                             │
    │  2. SEMINAR HASIL (SemHas)                                                  │
    │     - Sama seperti SemPro                                                   │
    │                                                                             │
    │  3. PRA-UJIAN                                                               │
    │     - Sama seperti SemPro                                                   │
    │                                                                             │
    │  4. UJIAN SKRIPSI                                                           │
    │     - Sama seperti SemPro                                                   │
    │     - Setelah lulus → Wisuda                                                │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  LIHAT NILAI (jika diizinkan oleh Kombi)                                   │
    │  /timeline/scores/{stage}                                                   │
    │  - Lihat detail nilai per tahap                                             │
    │  - Download PDF nilai                                                       │
    └─────────────────────────────────────────────────────────────────────────────┘
```

---

### 2. KOMBI (Komisi Bimbingan)

**Dashboard:**
- Overview statistik mahasiswa
- Menu verifikasi judul
- Menu penugasan dosen
- Menu jadwal seminar/ujian
- Menu verifikasi nilai
- Menu laporan

**Fitur yang Tersedia:**

| Menu | Deskripsi |
|------|-----------|
| `/titles/verify` | Verifikasi pengajuan judul skripsi |
| `/assignments/set` | Menugaskan pembimbing & penguji |
| `/events` | Mengelola jadwal seminar & ujian |
| `/verification/evaluation` | Verifikasi nilai masuk |
| `/verification/final` | Verifikasi nilai akhir |
| `/reports/*` | Laporan masa studi, SLA, kelulusan |
| `/settings` | Pengaturan sistem (angkatan aktif, visibility nilai) |
| `/students` | Kelola data mahasiswa |
| `/lecturers` | Kelola data dosen |
| `/users` | Kelola akun pengguna |

**Alur Penggunaan Kombi:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         ALUR PENGGUNAAN KOMBI                                   │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │   LOGIN  │
    └─────┬────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                              DASHBOARD KOMBI                                 │
    │  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐       │
    │  │ Judul        │ │ Penugasan    │ │ Jadwal       │ │ Verifikasi   │       │
    │  │ Pending      │ │ Dosen        │ │ Seminar      │ │ Nilai        │       │
    │  └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘       │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          ├─────────────────────────────────────────────────────────────────────────┤
          │                                                                         │
          v                                                                         v
    ┌─────────────────────┐                                               ┌─────────────────┐
    │  VERIFIKASI JUDUL   │                                               │  PENUGASAN DOSEN │
    │  /titles/verify     │                                               │  /assignments/set│
    └─────────┬───────────┘                                               └────────┬────────┘
              │                                                                    │
              v                                                                    v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  1. Review Judul                                                             │
    │     - Cek kesesuaian dengan topik                                           │
    │     - Cek ketersediaan dosen                                                │
    │     - Terima/Tolak dengan catatan                                           │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────┐     ┌─────────────────────┐
    │  SETUJU             │     │  TOLAK              │
    │  Lanjut Penugasan   │     │  Mahasiswa revisi   │
    └─────────┬───────────┘     └─────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  2. Penugasan Pembimbing & Penguji                                          │
    │     - Pilih Pembimbing 1 (Wajib)                                             │
    │     - Pilih Pembimbing 2 (Opsional)                                          │
    │     - Pilih Penguji 1/Ketua (Wajib)                                          │
    │     - Pilih Penguji 2 & 3 (Opsional)                                         │
    │     - Catatan penugasan                                                     │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  3. Buat Jadwal Seminar/Ujian                                               │
    │     /events                                                                  │
    │     - Pilih mahasiswa                                                        │
    │     - Pilih tahap (SemPro/SemHas/Ujian)                                      │
    │     - Set tanggal & waktu                                                   │
    │     - Undang penguji eksternal (jika perlu)                                  │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  4. Hari H Seminar/Ujian                                                    │
    │     - Monitor kehadiran dosen                                               │
    │     - Pastikan semua penguji input nilai                                     │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  5. Verifikasi Nilai                                                        │
    │     /verification/evaluation                                                 │
    │     - Review nilai yang masuk                                               │
    │     - Approve atau request revisi                                           │
    │     - Finalisasi nilai                                                      │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  6. Verifikasi Nilai Akhir                                                  │
    │     /verification/final                                                      │
    │     - Hitung nilai akhir                                                    │
    │     - Tentukan lulus/tidak                                                   │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  7. Laporan & Ekspor                                                        │
    │     /reports/*                                                               │
    │     - Laporan masa studi                                                    │
    │     - Laporan kelulusan                                                     │
    │     - Tracking mahasiswa                                                    │
    │     - SLA penilaian                                                         │
    └─────────────────────────────────────────────────────────────────────────────┘
```

---

### 3. DOSEN PEMBIMBING

**Dashboard:**
- Daftar mahasiswa bimbingan
- Status setiap mahasiswa
- Quick access ke input nilai

**Fitur yang Tersedia:**

| Menu | Deskripsi |
|------|-----------|
| `/scores/submit` | Input nilai untuk mahasiswa bimbingan |
| `/evaluations/form/{student_id}/{stage}` | Form penilaian detail |
| `/scores/export/{student_id}/{stage}` | Export PDF nilai |
| `/profile` | Mengubah profil dan password |

**Tahapan yang Dinilai:**
- Seminar Proposal (SemPro)
- Seminar Hasil (SemHas)
- Pra-Ujian

**Alur Penggunaan Dosen Pembimbing:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                    ALUR PENGGUNAAN DOSEN PEMBIMBING                             │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │   LOGIN  │
    └─────┬────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                         DASHBOARD PEMBIMBING                                │
    │  ┌───────────────────────────────────────────────────────────────────────┐  │
    │  │  Daftar Mahasiswa Bimbingan                                           │  │
    │  │  ┌─────────────────────────────────────────────────────────────────┐  │  │
    │  │  │ Nama    | NIM   | Tahap          | Status          | Aksi       │  │  │
    │  │  │─────────|-------|----------------|-----------------|------------│  │  │
    │  │  │ Budi    | 12345 | SemPro         | Terjadwal      | Input Nilai│  │  │
    │  │  │ Ani     | 12346 | SemHas         | Menunggu Nilai │ Input Nilai│  │  │
    │  │  │ Citra   | 12347 | Pra-Ujian      | Selesai        | Lihat Nilai│  │  │
    │  │  └─────────────────────────────────────────────────────────────────┘  │  │
    │  └───────────────────────────────────────────────────────────────────────┘  │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  INPUT NILAI                                                                │
    │  /scores/submit                                                              │
    │                                                                             │
    │  Filter:                                                                    │
    │  - Angkatan                                                                 │
    │  - Search (nama/NIM)                                                        │
    │                                                                             │
    │  Daftar Mahasiswa untuk Dinilai:                                            │
    │  ┌─────────────────────────────────────────────────────────────────────┐   │
    │  │ Mahasiswa: Budi Santoso                                              │   │
    │  │ Tahap: Seminar Proposal (SemPro)                                     │   │
    │  │ Jadwal: 15 Januari 2026, 09:00 WIB                                   │   │
    │  │ Status: BELUM DINILAI                                                │   │
    │  │                                                                      │   │
    │  │ [Input Nilai SemPro]  [Input Nilai SemHas]  [Input Nilai Pra-Ujian]  │   │
    │  └─────────────────────────────────────────────────────────────────────┘   │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  FORM PENILAIAN                                                             │
    │  /evaluations/form/{student_id}/{stage}                                     │
    │                                                                             │
    │  Komponen Penilaian (contoh SemPro):                                        │
    │  ┌─────────────────────────────────────────────────────────────────────┐   │
    │  │ 1. Penulisan Proposal           (0-100)  : [____]                    │   │
    │  │ 2. Presentasi                   (0-100)  : [____]                    │   │
    │  │ 3. Pemahaman Materi             (0-100)  : [____]                    │   │
    │  │ 4. Kelayakan Metode             (0-100)  : [____]                    │   │
    │  │ 5. Catatan/Revisi:                                                │   │
    │  │    ┌────────────────────────────────────────────────────────────┐   │   │
    │  │    │                                                            │   │   │
    │  │    └────────────────────────────────────────────────────────────┘   │   │
    │  │                                                                      │   │
    │  │  NILAI AKHIR: [Auto-calculated]                                       │   │
    │  │                                                                      │   │
    │  │  [Simpan Nilai]  [Batal]                                             │   │
    │  └─────────────────────────────────────────────────────────────────────┘   │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  SETELAH SIMPAN                                                             │
    │  - Nilai tersimpan di database                                              │
    │  - Menunggu verifikasi Kombi                                               │
    │  - Bisa edit sebelum diverifikasi                                          │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  EXPORT NILAI (PDF)                                                         │
    │  /scores/export/{student_id}/{stage}                                        │
    │  - Download berita acara penilaian dalam format PDF                         │
    └─────────────────────────────────────────────────────────────────────────────┘
```

---

### 4. DOSEN PENGUJI

**Dashboard:**
- Daftar mahasiswa yang akan diuji
- Jadwal ujian
- Quick access ke input nilai

**Fitur yang Tersedia:**

| Menu | Deskripsi |
|------|-----------|
| `/scores/submit` | Input nilai untuk mahasiswa yang diuji |
| `/evaluations/form/{student_id}/{stage}` | Form penilaian detail |
| `/scores/export/{student_id}/{stage}` | Export PDF nilai |
| `/profile` | Mengubah profil dan password |

**Tahapan yang Dinilai:**
- Seminar Proposal (SemPro)
- Seminar Hasil (SemHas)
- Ujian Skripsi

**Alur Penggunaan Dosen Penguji:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                     ALUR PENGGUNAAN DOSEN PENGUJI                               │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │   LOGIN  │
    └─────┬────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                         DASHBOARD PENGUJI                                   │
    │  ┌───────────────────────────────────────────────────────────────────────┐  │
    │  │  Jadwal Ujian Mendatang                                               │  │
    │  │  ┌─────────────────────────────────────────────────────────────────┐  │  │
    │  │  │ Tanggal     | Waktu  | Mahasiswa    | Tahap   | Ruang | Aksi    │  │  │
    │  │  │─────────────|--------|--------------|---------|-------|---------│  │  │
    │  │  │ 15 Jan 2026 │ 09:00  | Budi Santoso │ SemPro  | A-101 | Detail  │  │  │
    │  │  │ 20 Jan 2026 │ 13:00  | Ani Wijaya   | SemHas  | B-205 | Detail  │  │  │
    │  │  │ 25 Jan 2026 │ 10:00  | Citra Dewi   | Ujian   | A-101 | Detail  │  │  │
    │  │  └─────────────────────────────────────────────────────────────────┘  │  │
    │  └───────────────────────────────────────────────────────────────────────┘  │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  PADA HARI H UJIAN                                                          │
    │  1. Hadir di ruang ujian pada waktu yang ditentukan                         │
    │  2. Ikuti proses ujian                                                      │
    │  3. Setelah ujian selesai, input nilai                                     │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  INPUT NILAI                                                                │
    │  /scores/submit                                                              │
    │                                                                             │
    │  Daftar Mahasiswa untuk Dinilai:                                            │
    │  ┌─────────────────────────────────────────────────────────────────────┐   │
    │  │ Mahasiswa: Budi Santoso                                              │   │
    │  │ Tahap: Seminar Proposal (SemPro)                                     │   │
    │  │ Peran: Penguji Ketua                                                 │   │
    │  │ Jadwal: 15 Januari 2026, 09:00 WIB                                   │   │
    │  │ Status: SUDAH DIUJI - BELUM DINILAI                                  │   │
    │  │                                                                      │   │
    │  │ [Input Nilai]                                                         │   │
    │  └─────────────────────────────────────────────────────────────────────┘   │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  FORM PENILAIAN                                                             │
    │  /evaluations/form/{student_id}/{stage}                                     │
    │                                                                             │
    │  Komponen Penilaian (contoh Ujian Skripsi):                                 │
    │  ┌─────────────────────────────────────────────────────────────────────┐   │
    │  │ 1. Presentasi                   (0-100)  : [____]                    │   │
    │  │ 2. Pemahaman Materi             (0-100)  : [____]                    │   │
    │  │ 3. Kemampuan Mempertahankan     (0-100)  : [____]                    │   │
    │  │ 4. Kualitas Skripsi             (0-100)  : [____]                    │   │
    │  │ 5. Catatan/Revisi:                                                │   │
    │  │    ┌────────────────────────────────────────────────────────────┐   │   │
    │  │    │                                                            │   │   │
    │  │    └────────────────────────────────────────────────────────────┘   │   │
    │  │                                                                      │   │
    │  │  REKOMENDASI:                                                         │   │
    │  │  ○ Lulus                                                              │   │
    │  │  ○ Lulus dengan Revisi                                                │   │
    │  │  ○ Tidak Lulus                                                        │   │
    │  │                                                                      │   │
    │  │  NILAI AKHIR: [Auto-calculated]                                       │   │
    │  │                                                                      │   │
    │  │  [Simpan Nilai]  [Batal]                                             │   │
    │  └─────────────────────────────────────────────────────────────────────┘   │
    └─────────────────────────────────────────────────────────────────────────────┘
```

---

### 5. PENGUJI EKSTERNAL

**Fitur yang Tersedia:**

| Menu | Deskripsi |
|------|-----------|
| `/external/login` | Login menggunakan token dari undangan |
| `/external/score/submit` | Input nilai untuk mahasiswa yang diuji |

**Alur Penggunaan Penguji Eksternal:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                   ALUR PENGGUNAAN PENGUJI EKSTERNAL                             │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  1. Menerima Undangan dari Kombi                                            │
    │     - Email berisi link login                                               │
    │     - Token akses unik                                                      │
    └─────────────────────────────────────────────────────────────────────────────┘
                              │
                              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  2. Login dengan Token                                                       │
    │     /external/login                                                          │
    │     - Masukkan NIP                                                          │
    │     - Masukkan Token dari undangan                                          │
    └─────────────────────────────────────────────────────────────────────────────┘
                              │
                              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  3. Dashboard Penguji Eksternal                                             │
    │     - Lihat jadwal ujian                                                    │
    │     - Akses form penilaian                                                  │
    └─────────────────────────────────────────────────────────────────────────────┘
                              │
                              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  4. Input Nilai (sama seperti Dosen Penguji)                                │
    │     /external/score/submit                                                  │
    └─────────────────────────────────────────────────────────────────────────────┘
                              │
                              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  5. Logout                                                                  │
    │     /external/logout                                                        │
    └─────────────────────────────────────────────────────────────────────────────┘
```

---

### 6. SUPERADMIN

**Dashboard:**
- Overview sistem
- Akses penuh ke semua fitur
- Manajemen pengguna
- Pengaturan sistem

**Fitur yang Tersedia:**

| Menu | Deskripsi |
|------|-----------|
| Semua menu Kombi | + akses tambahan |
| `/users` | Kelola akun pengguna (CRUD) |
| `/users/roles` | Kelola role pengguna |
| `/users/reset-password` | Reset password pengguna |
| `/settings` | Pengaturan sistem lengkap |
| `/backups` | Backup & restore database |

**Alur Penggunaan Superadmin:**

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                      ALUR PENGGUNAAN SUPERADMIN                                 │
└─────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │   LOGIN  │
    └─────┬────┘
          │
          v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │                         DASHBOARD SUPERADMIN                                 │
    │  - Statistik sistem                                                         │
    │  - Akses cepat ke semua modul                                               │
    └─────────────────────────────────────────────────────────────────────────────┘
          │
          ├─────────────────────────────────────────────────────────────────────────┤
          │                                                                         │
          v                                                                         v
    ┌─────────────────────┐                                               ┌─────────────────┐
    │  MANAJEMEN USER     │                                               │  PENGATURAN      │
    │  /users             │                                               │  /settings      │
    └─────────┬───────────┘                                               └────────┬────────┘
              │                                                                    │
              v                                                                    v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  Kelola User:                                                                │
    │  - Tambah user baru                                                          │
    │  - Edit user (nama, username, role)                                          │
    │  - Reset password                                                            │
    │  - Hapus user                                                                │
    │  - Import user dari CSV                                                     │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  Pengaturan Sistem:                                                          │
    │  - Angkatan aktif                                                            │
    │  - Visibility nilai per tahap                                                │
    │  - Konfigurasi backup                                                        │
    │  - Konfigurasi SMTP                                                          │
    └─────────────────────────────────────────────────────────────────────────────┘
              │
              v
    ┌─────────────────────────────────────────────────────────────────────────────┐
    │  Backup & Restore:                                                           │
    │  - Backup database otomatis/manuel                                          │
    │  - Restore dari backup                                                       │
    │  - Sync ke SMB storage                                                       │
    └─────────────────────────────────────────────────────────────────────────────┘
```

---

## Flowchart Lengkap

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                                                                                                             │
│                                                    FLOWCHART SIKLUS SKRIPSI - KBS (Komisi Bimbingan Skripsi)                                                          │
│                                                                                                                                                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                                                                                                             │
│  ┌──────────────────┐                                                                                                                                                      │
│  │   AWAL: MAHASISWA │                                                                                                                                                     │
│  │   DAFTAR AKTIF   │                                                                                                                                                     │
│  └────────┬─────────┘                                                                                                                                                      │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     1. MAHASISWA AJUKAN JUDUL                                                          │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /titles/submit                                                                                                                             │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Input:                                                                                                                                      │  │  │
│  │  │  - Judul Skripsi                                                                                                                            │  │  │
│  │  │  - Abstrk                                                                                                                                   │  │  │
│  │  │  - Kata Kunci                                                                                                                               │  │  │
│  │  │  - Usulan Pembimbing 1 & 2                                                                                                                  │  │  │
│  │  │  - Usulan Penguji 1, 2, & 3                                                                                                                 │  │  │
│  │  │  - Link Surat Tugas (jika ada)                                                                                                              │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  [Simpan & Ajukan]                                                                                                                         │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     2. KOMBI VERIFIKASI JUDUL                                                            │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /titles/verify                                                                                                                              │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Review:                                                                                                                                     │  │  │
│  │  │  - Kesesuaian topik                                                                                                                         │  │  │
│  │  │  - Ketersediaan dosen                                                                                                                       │  │  │
│  │  │  - Orisinalitas judul                                                                                                                       │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │         ┌─────────────┐                                                                                                                    │  │  │
│  │  │         │  DITERIMA   │                                                                                                                    │  │  │
│  │  │         └──────┬──────┘                                                                                                                    │  │  │
│  │  │                │                                                                                                                          │  │  │
│  │  │                v                                                                                                                          │  │  │
│  │  │         ┌─────────────┐     ┌─────────────┐                                                                                               │  │  │
│  │  │         │  DITOLAK    │     │  REVISI     │                                                                                               │  │  │
│  │  │         └──────┬──────┘     └──────┬──────┘                                                                                               │  │  │
│  │  │                │                    │                                                                                                      │  │  │
│  │  │                v                    v                                                                                                      │  │  │
│  │  │         [Mahasiswa        [Mahasiswa                                                                                                       │  │  │
│  │  │          ajukan ulang]     revisi judul]                                                                                                    │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │ (Jika diterima)                                                                                                                                                 │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     3. KOMBI SET PEMBIMBING & PENGUI                                                     │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /assignments/set                                                                                                                            │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Pilih:                                                                                                                                      │  │  │
│  │  │  - Pembimbing 1 (Wajib)                                                                                                                     │  │  │
│  │  │  - Pembimbing 2 (Opsional)                                                                                                                  │  │  │
│  │  │  - Penguji 1/Ketua (Wajib)                                                                                                                  │  │  │
│  │  │  - Penguji 2 (Opsional)                                                                                                                    │  │  │
│  │  │  - Penguji 3 (Opsional)                                                                                                                    │  │  │
│  │  │  - Catatan penugasan                                                                                                                       │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  [Simpan Penugasan]                                                                                                                        │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     4. PROSES BIMBINGAN                                                                │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │  - Mahasiswa konsultasi dengan pembimbing                                                                                                   │  │  │
│  │  │  - Penyusunan proposal skripsi                                                                                                               │  │  │
│  │  │  - Persiapan seminar proposal                                                                                                                │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     5. KOMBI BUAT JADWAL SEMPRO                                                         │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /events/create                                                                                                                              │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Input:                                                                                                                                      │  │  │
│  │  │  - Mahasiswa                                                                                                                                 │  │  │
│  │  │  - Tahap: Sempro                                                                                                                            │  │  │
│  │  │  - Tanggal & Waktu                                                                                                                          │  │  │
│  │  │  - Ruang                                                                                                                                    │  │  │
│  │  │  - Undang penguji eksternal (opsional)                                                                                                      │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  [Simpan Jadwal]                                                                                                                            │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     6. HARI H SEMPRO                                                                    │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │  - Mahasiswa presentasi proposal                                                                                                            │  │  │
│  │  │  - Pembimbing & Penguji hadir                                                                                                               │  │  │
│  │  │  - Tanya jawab                                                                                                                              │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     7. DOSEN INPUT NILAI SEMPRO                                                         │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /scores/submit → /evaluations/form/{student_id}/sempro                                                                                       │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Pembimbing input:                                                                                                                          │  │  │
│  │  │  - Penulisan Proposal                                                                                                                       │  │  │
│  │  │  - Presentasi                                                                                                                               │  │  │
│  │  │  - Pemahaman Materi                                                                                                                        │  │  │
│  │  │  - Kelayakan Metode                                                                                                                        │  │  │
│  │  │  - Catatan Revisi                                                                                                                          │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Penguji input:                                                                                                                            │  │  │
│  │  │  - Presentasi                                                                                                                               │  │  │
│  │  │  - Pemahaman Materi                                                                                                                        │  │  │
│  │  │  - Kualitas Proposal                                                                                                                       │  │  │
│  │  │  - Catatan Revisi                                                                                                                          │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  [Simpan Nilai]                                                                                                                            │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     8. KOMBI VERIFIKASI NILAI SEMPRO                                                     │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /verification/evaluation                                                                                                                     │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Review nilai dari semua dosen:                                                                                                             │  │  │
│  │  │  - Cek kelengkapan                                                                                                                         │  │  │
│  │  │  - Approve atau request revisi                                                                                                             │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  [Approve Sempro]                                                                                                                          │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     9. SEMHAS → PRA-UJIAN → UJIAN (Ulangi langkah 5-8)                                     │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │  Untuk setiap tahap (Semhas, Pra-Ujian, Ujian):                                                                                             │  │  │
│  │  │  1. Kombi buat jadwal                                                                                                                       │  │  │
│  │  │  2. Hari H pelaksanaan                                                                                                                      │  │  │
│  │  │  3. Dosen input nilai                                                                                                                       │  │  │
│  │  │  4. Kombi verifikasi nilai                                                                                                                  │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Catatan:                                                                                                                                   │  │  │
│  │  │  - Semhas & Pra-Ujian: Pembimbing + Penguji                                                                                                │  │  │
│  │  │  - Ujian: Penguji saja (Pembimbing tidak menilai)                                                                                           │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     10. KOMBI VERIFIKASI NILAI AKHIR & KELULUSAN                                            │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /verification/final                                                                                                                         │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Hitung nilai akhir dari semua tahap:                                                                                                       │  │  │
│  │  │  - Sempro: X%                                                                                                                              │  │  │
│  │  │  - Semhas: X%                                                                                                                              │  │  │
│  │  │  - Pra-Ujian: X%                                                                                                                           │  │  │
│  │  │  - Ujian: X%                                                                                                                               │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Tentukan:                                                                                                                                  │  │  │
│  │  │  - Lulus (Nilai ≥ Batas Lulus)                                                                                                              │  │  │
│  │  │  - Tidak Lulus (Nilai < Batas Lulus)                                                                                                       │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  [Finalisasi Kelulusan]                                                                                                                    │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     11. LAPORAN & EKSPOR                                                               │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │ /reports/*                                                                                                                                  │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Kombi/Superadmin generate laporan:                                                                                                        │  │  │
│  │  │  - Laporan masa studi                                                                                                                      │  │  │
│  │  │  - Laporan kelulusan                                                                                                                       │  │  │
│  │  │  - Tracking mahasiswa                                                                                                                      │  │  │
│  │  │  - SLA penilaian                                                                                                                           │  │  │
│  │  │  - Export PDF nilai per mahasiswa per tahap                                                                                                │  │  │
│  │  │                                                                                                                                             │  │  │
│  │  │  Mahasiswa:                                                                                                                                 │  │  │
│  │  │  - Lihat timeline di /timeline                                                                                                             │  │  │
│  │  │  - Lihat nilai (jika dibuka oleh Kombi) di /timeline/scores/{stage}                                                                        │  │  │
│  │  │  - Download PDF nilai                                                                                                                      │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│           │                                                                                                                                                                │
│           v                                                                                                                                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │
│  │                                                                                     12. SELESAI - WISUDA                                                               │  │
│  │  ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐  │  │
│  │  │  Mahasiswa dinyatakan LULUS dan dapat mengikuti wisuda                                                                                       │  │  │
│  │  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │  │
│  └─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                                                                                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Ringkasan Rute (Routes) Utama

### Rute Autentikasi
| Route | Controller | Method | Deskripsi |
|-------|-----------|--------|-----------|
| `/login` | `AuthController` | `login` | Halaman login |
| `/auth/login` | `AuthController` | `loginProcess` | Proses login |
| `/logout` | `AuthController` | `logout` | Logout |
| `/auth/change-password` | `AuthController` | `changePassword` | Ubah password |

### Rute Mahasiswa
| Route | Controller | Method | Deskripsi |
|-------|-----------|--------|-----------|
| `/titles/submit` | `TitleController` | `submit` | Ajukan judul |
| `/timeline` | `TimelineController` | `index` | Lihat timeline |
| `/timeline/scores/{stage}` | `TimelineController` | `scoreDetail` | Detail nilai |
| `/profile` | `ProfileController` | `index` | Profil |

### Rute Kombi
| Route | Controller | Method | Deskripsi |
|-------|-----------|--------|-----------|
| `/titles/verify` | `TitleController` | `verify` | Verifikasi judul |
| `/assignments/set` | `AssignmentController` | `set` | Set pembimbing/penguji |
| `/events` | `EventController` | `index` | Kelola jadwal |
| `/verification/evaluation` | `VerificationController` | `evaluation` | Verifikasi nilai |
| `/verification/final` | `VerificationController` | `finalScore` | Verifikasi nilai akhir |
| `/reports/*` | `ReportController` | various | Laporan |

### Rute Dosen
| Route | Controller | Method | Deskripsi |
|-------|-----------|--------|-----------|
| `/scores/submit` | `ScoreController` | `submit` | Input nilai |
| `/evaluations/form/{student_id}/{stage}` | `EvaluationController` | `showForm` | Form penilaian |
| `/scores/export/{student_id}/{stage}` | `ScoreController` | `exportStage` | Export PDF |

### Rute Superadmin
| Route | Controller | Method | Deskripsi |
|-------|-----------|--------|-----------|
| `/users` | `UserController` | `index` | Kelola user |
| `/users/roles` | `UserController` | `manageRoles` | Kelola role |
| `/settings` | `SettingsController` | `index` | Pengaturan sistem |

---

## Catatan Penting

1. **Blind Grading**: Mahasiswa tidak pernah melihat angka nilai secara langsung. Nilai hanya dapat dilihat jika Kombi mengaktifkan visibility untuk tahap tertentu.

2. **Audit Trail**: Setiap aksi penting (penugasan, penilaian, verifikasi) dicatat dalam audit log.

3. **Bypass Nilai**: Jika dosen tidak dapat menginput nilai, Kombi dapat melakukan bypass dengan alasan yang jelas dan dicatat dalam audit trail.

4. **Angkatan Aktif**: Sistem dapat difilter berdasarkan angkatan aktif untuk fokus pada mahasiswa tertentu.

5. **Penguji Eksternal**: Login menggunakan token yang dikirim melalui undangan, tidak perlu akun permanen.

---

*Dokumentasi ini dibuat berdasarkan analisis kode sumber aplikasi KBS (Komisi Bimbingan Skripsi) untuk Prodi Teknologi Industri Pertanian.*
