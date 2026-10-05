# BPMN Alur Kerja Aplikasi KBS (Komisi Bimbingan Skripsi)

## Diagram Alur Kerja per Role (BPMN Style)

---

## 1. MAHASISWA - Alur Kerja Lengkap

```mermaid
flowchart TD
    Start([Start]) --> Login[Login ke Aplikasi]
    Login --> CekTimeline{Cek Timeline}
    
    CekTimeline -->|Belum Ada Judul| AjukanJudul[Ajukan Judul Skripsi]
    CekTimeline -->|Sudah Ada Judul| PantauProgres[Pantau Progres Skripsi]
    
    AjukanJudul --> IsiForm[Isi Formulir Judul]
    IsiForm --> PilihDosen[Pilih Dosen Pembimbing & Penguji]
    PilihDosen --> Kirim[Kirim Pengajuan]
    Kirim --> TungguVerifikasi[Tunggu Verifikasi Kombi]
    
    TungguVerifikasi --> VerifikasiResult{Hasil Verifikasi}
    VerifikasiResult -->|Diterima| TerimaJudul[Judul Diterima]
    VerifikasiResult -->|Ditolak| Revisi[Revisi Judul]
    VerifikasiResult -->|Perlu Revisi| Revisi
    
    Revisi --> AjukanJudul
    
    TerimaJudum --> TungguPenugasan[Tunggu Penugasan Dosen]
    TungguPenugasan --> ProsesBimbingan[Proses Bimbingan dengan Pembimbing]
    
    ProsesBimbingan --> CekJadwal{Ada Jadwal Seminar/Ujian?}
    CekJadwal -->|Belum| ProsesBimbingan
    CekJadwal -->|Ada| IkutiSeminar[Ikuti Seminar/Ujian]
    
    IkutiSeminar --> TungguNilai[Tunggu Penilaian Dosen]
    TungguNilai --> CekStatus{Status Nilai}
    CekStatus -->|Belum Diverifikasi| TungguNilai
    CekStatus -->|Sudah Diverifikasi| CekTahap{Masih Ada Tahap Berikutnya?}
    
    CekTahap -->|Ya| CekJadwal
    CekTahap -->|Tidak| CekKelulusan{Status Kelulusan}
    
    CekKelulusan -->|Lulus| LihatNilai[Lihat Nilai & Download PDF]
    CekKelulusan -->|Tidak Lulus| Ulangi[Ulangi Tahap]
    
    LihatNilai --> End([End - Wisuda])
    Ulangi --> CekJadwal
    
    PantauProgres --> CekStatusTimeline{Cek Status di Timeline}
    CekStatusTimeline -->|Proses Belum Selesai| PantauProgres
    CekStatusTimeline -->|Semua Selesai| CekKelulusan
```

### BPMN Notation - Mahasiswa

| Simbol | Deskripsi |
|--------|-----------|
| ⚪ Start Event | Memulai proses |
| ⬛ Task/Activity | Aktivitas yang dilakukan |
| ◇ Gateway | Keputusan/branching |
| ⚫ End Event | Selesai proses |

---

## 2. KOMBI - Alur Kerja Lengkap

```mermaid
flowchart TD
    Start([Start]) --> Login[Login ke Aplikasi]
    Login --> Dashboard[Dashboard Kombi]
    
    Dashboard --> CekActivity{Pilih Aktivitas}
    
    CekActivity -->|Verifikasi Judul| VerifikasiJudul[Review Judul Mahasiswa]
    CekActivity -->|Penugasan Dosen| PenugasanDosen[Set Pembimbing & Penguji]
    CekActivity -->|Buat Jadwal| BuatJadwal[Buat Jadwal Seminar/Ujian]
    CekActivity -->|Verifikasi Nilai| VerifikasiNilai[Review Nilai Dosen]
    CekActivity -->|Laporan| GenerateLaporan[Generate Laporan]
    
    subgraph "Proses Verifikasi Judul"
        VerifikasiJudul --> ReviewJudul[Review Kesesuaian Topik]
        ReviewJudul --> CekKetersediaan{Cek Ketersediaan Dosen}
        CekKetersediaan -->|Tersedia| Terima[Terima Judul]
        CekKetersediaan -->|Tidak Tersedia| Tolak[Tolak Judul]
        CekKetersediaan -->|Perlu Revisi| MintaRevisi[Minta Revisi]
    end
    
    Terima --> NotifikasiMahasiswa[Notifikasi ke Mahasiswa]
    Tolak --> NotifikasiMahasiswa
    MintaRevisi --> NotifikasiMahasiswa
    
    subgraph "Proses Penugasan Dosen"
        PenugasanDosen --> PilihMahasiswa[Pilih Mahasiswa]
        PilihMahasiswa --> PilihPembimbing1[Pilih Pembimbing 1]
        PilihPembimbing1 --> PilihPembimbing2{Pilih Pembimbing 2?}
        PilihPembimbing2 -->|Ya| PilihPembimbing2Yes[Pilih Pembimbing 2]
        PilihPembimbing2 -->|Tidak| PilihPenguji1
        PilihPembimbing2Yes --> PilihPenguji1[Pilih Penguji 1/Ketua]
        PilihPenguji1 --> PilihPenguji2{Pilih Penguji 2?}
        PilihPenguji2 -->|Ya| PilihPenguji2Yes[Pilih Penguji 2]
        PilihPenguji2 -->|Tidak| PilihPenguji3
        PilihPenguji2Yes --> PilihPenguji3{Pilih Penguji 3?}
        PilihPenguji3 -->|Ya| PilihPenguji3Yes[Pilih Penguji 3]
        PilihPenguji3 -->|Tidak| SimpanPenugasan
        PilihPenguji3Yes --> SimpanPenugasan[Simpan Penugasan]
    end
    
    SimpanPenugasan --> NotifikasiDosen[Notifikasi ke Dosen]
    
    subgraph "Proses Pembuatan Jadwal"
        BuatJadwal --> PilihTahap[Pilih Tahap: Sempro/Semhas/Ujian]
        PilihTahap --> SetTanggal[Set Tanggal & Waktu]
        SetTanggal --> SetRuang[Set Ruangan]
        SetRuang --> UndangEksternal{Undang Penguji Eksternal?}
        UndangEksternal -->|Ya| GenerateToken[Generate Token & Kirim Undangan]
        UndangEksternal -->|Tidak| SimpanJadwal
        GenerateToken --> SimpanJadwal[Simpan Jadwal]
    end
    
    SimpanJadwal --> NotifikasiAll[Notifikasi ke Semua Pihak]
    
    subgraph "Proses Verifikasi Nilai"
        VerifikasiNilai --> CekKelengkapan{Cek Kelengkapan Nilai}
        CekKelengkapan -->|Belum Lengkap| IngatkanDosen[Ingatkan Dosen]
        CekKelengkapan -->|Lengkap| ReviewNilai[Review Nilai]
        ReviewNilai --> Validasi{Validasi Nilai}
        Validasi -->|OK| Approve[Approve Nilai]
        Validasi -->|Ada Masalah| RequestRevisi[Request Revisi ke Dosen]
    end
    
    RequestRevisi --> IngatkanDosen
    Approve --> CekTahapSelesai{Semua Tahap Selesai?}
    
    CekTahapSelesai -->|Tidak| Dashboard
    CekTahapSelesai -->|Ya| HitungNilaiAkhir[Hitung Nilai Akhir]
    
    HitungNilaiAkhir --> TentukanKelulusan{Tentukan Kelulusan}
    TentukanKelulusan -->|Lulus| Lulus[Mark Lulus]
    TentukanKelulusan -->|Tidak Lulus| TidakLulus[Mark Tidak Lulus]
    
    Lulus --> GenerateLaporan
    TidakLulus --> NotifikasiUlangi[Notifikasi untuk Ulang]
    
    subgraph "Generate Laporan"
        GenerateLaporan --> PilihJenisLaporan{Pilih Jenis Laporan}
        PilihJenisLaporan -->|Masa Studi| LaporanMasaStudi
        PilihJenisLaporan -->|Kelulusan| LaporanKelulusan
        PilihJenisLaporan -->|Tracking| LaporanTracking
        PilihJenisLaporan -->|SLA| LaporanSLA
        LaporanMasaStudi --> ExportPDF
        LaporanKelulusan --> ExportPDF
        LaporanTracking --> ExportPDF
        LaporanSLA --> ExportPDF[Export PDF/Excel]
    end
    
    ExportPDF --> End([End])
    NotifikasiMahasiswa --> Dashboard
    NotifikasiDosen --> Dashboard
    NotifikasiAll --> Dashboard
    IngatkanDosen --> VerifikasiNilai
    NotifikasiUlangi --> Dashboard
```

---

## 3. DOSEN PEMBIMBING - Alur Kerja

```mermaid
flowchart TD
    Start([Start]) --> Login[Login ke Aplikasi]
    Login --> Dashboard[Dashboard Pembimbing]
    
    Dashboard --> CekMahasiswa{Lihat Mahasiswa Bimbingan}
    
    CekMahasiswa --> ListMahasiswa[Daftar Mahasiswa Bimbingan]
    ListMahasiswa --> PilihMahasiswa[Pilih Mahasiswa]
    
    PilihMahasiswa --> CekStatus{Status Mahasiswa}
    
    CekStatus -->|Perlu Bimbingan| ProsesBimbingan[Proses Bimbingan]
    CekStatus -->|Ada Jadwal Seminar/Ujian| CekTahap{Cek Tahap}
    
    ProsesBimbingan --> Konsultasi[Konsultasi dengan Mahasiswa]
    Konsultasi --> BerikanFeedback[Berikan Feedback]
    BerikanFeedback --> CekStatus
    
    CekTahap -->|Sempro| InputNilaiSempro[Input Nilai Sempro]
    CekTahap -->|Semhas| InputNilaiSemhas[Input Nilai Semhas]
    CekTahap -->|Pra-Ujian| InputNilaiPraUjian[Input Nilai Pra-Ujian]
    
    subgraph "Input Nilai Sempro"
        InputNilaiSempro --> IsiFormSempro[Isi Form Penilaian]
        IsiFormSempro --> PenulisanProposal[Nilai Penulisan Proposal]
        PenulisanProposal --> Presentasi[Nilai Presentasi]
        Presentasi --> Pemahaman[Nilai Pemahaman Materi]
        Pemahaman --> Kelayakan[Nilai Kelayakan Metode]
        Kelayakan --> CatatanRevisi[Catatan Revisi]
        CatatanRevisi --> SimpanNilaiSempro[Simpan Nilai]
    end
    
    subgraph "Input Nilai Semhas"
        InputNilaiSemhas --> IsiFormSemhas[Isi Form Penilaian]
        IsiFormSemhas --> PenulisanLaporan[Nilai Penulisan Laporan]
        PenulisanLaporan --> PresentasiSemhas[Nilai Presentasi]
        PresentasiSemhas --> PemahamanSemhas[Nilai Pemahaman]
        PemahamanSemhas --> KualitasData[Nilai Kualitas Data]
        KualitasData --> CatatanRevisiSemhas[Catatan Revisi]
        CatatanRevisiSemhas --> SimpanNilaiSemhas[Simpan Nilai]
    end
    
    subgraph "Input Nilai Pra-Ujian"
        InputNilaiPraUjian --> IsiFormPra[Isi Form Penilaian]
        IsiFormPra --> KelengkapanDokumen[Nilai Kelengkapan Dokumen]
        KelengkapanDokumen --> KualitasDraft[Nilai Kualitas Draft Skripsi]
        KualitasDraft --> KesiapanUjian[Nilai Kesiapan Ujian]
        KesiapanUjian --> CatatanRevisiPra[Catatan Revisi]
        CatatanRevisiPra --> SimpanNilaiPra[Simpan Nilai]
    end
    
    SimpanNilaiSempro --> TungguVerifikasi[Tunggu Verifikasi Kombi]
    SimpanNilaiSemhas --> TungguVerifikasi
    SimpanNilaiPra --> TungguVerifikasi
    
    TungguVerifikasi --> CekVerifikasi{Status Verifikasi}
    CekVerifikasi -->|Belum Diverifikasi| TungguVerifikasi
    CekVerifikasi -->|Sudah Diverifikasi| ExportPDF[Export PDF Nilai]
    
    ExportPDF --> End([End])
```

---

## 4. DOSEN PENGUJI - Alur Kerja

```mermaid
flowchart TD
    Start([Start]) --> Login[Login ke Aplikasi]
    Login --> Dashboard[Dashboard Penguji]
    
    Dashboard --> CekJadwal{Lihat Jadwal Ujian}
    
    CekJadwal --> ListJadwal[Daftar Jadwal Ujian]
    ListJadwal --> PilihJadwal[Pilih Jadwal]
    
    PilihJadwal --> CekWaktu{Cek Waktu Ujian}
    CekWaktu -->|Belum Waktunya| Tunggu[Tunggu Hari H]
    CekWaktu -->|Sudah Lewat| InputNilai[Input Nilai]
    
    Tunggu --> HariH[Hari H Ujian]
    HariH --> Hadir[Hadir di Ruang Ujian]
    Hadir --> IkutiUjian[Ikuti Proses Ujian]
    IkutiUjian --> TanyaJawab[Sesi Tanya Jawab]
    TanyaJawab --> SelesaiUjian[Ujian Selesai]
    SelesaiUjian --> InputNilai
    
    subgraph "Input Nilai"
        InputNilai --> PilihTahap{Pilih Tahap}
        PilihTahap -->|Sempro| InputSempro[Input Nilai Sempro]
        PilihTahap -->|Semhas| InputSemhas[Input Nilai Semhas]
        PilihTahap -->|Ujian| InputUjian[Input Nilai Ujian]
        
        subgraph "Input Nilai Sempro/Semhas"
            InputSempro --> IsiForm1[Isi Form Penilaian]
            InputSemhas --> IsiForm1
            IsiForm1 --> NilaiPresentasi[Nilai Presentasi]
            NilaiPresentasi --> NilaiPemahaman[Nilai Pemahaman Materi]
            NilaiPemahaman --> NilaiKualitas[Nilai Kualitas Proposal/Laporan]
            NilaiKualitas --> CatatanRevisi1[Catatan Revisi]
            CatatanRevisi1 --> Simpan1[Simpan Nilai]
        end
        
        subgraph "Input Nilai Ujian"
            InputUjian --> IsiForm2[Isi Form Penilaian]
            IsiForm2 --> NilaiPresentasiUjian[Nilai Presentasi]
            NilaiPresentasiUjian --> NilaiPemahamanUjian[Nilai Pemahaman]
            NilaiPemahamanUjian --> NilaiPembelaan[Nilai Kemampuan Mempertahankan]
            NilaiPembelaan --> NilaiSkripsi[Nilai Kualitas Skripsi]
            NilaiSkripsi --> Rekomendasi{Rekomendasi}
            Rekomendasi -->|Lulus| Lulus[Lulus]
            Rekomendasi -->|Lulus dengan Revisi| LulusRevisi[Lulus dengan Revisi]
            Rekomendasi -->|Tidak Lulus| TidakLulus[Tidak Lulus]
            Lulus --> CatatanRevisi2[Catatan Revisi]
            LulusRevisi --> CatatanRevisi2
            TidakLulus --> CatatanRevisi2
            CatatanRevisi2 --> Simpan2[Simpan Nilai]
        end
    end
    
    Simpan1 --> TungguVerifikasi[Tunggu Verifikasi Kombi]
    Simpan2 --> TungguVerifikasi
    
    TungguVerifikasi --> CekVerifikasi{Status Verifikasi}
    CekVerifikasi -->|Belum Diverifikasi| TungguVerifikasi
    CekVerifikasi -->|Sudah Diverifikasi| ExportPDF[Export PDF Nilai]
    
    ExportPDF --> End([End])
```

---

## 5. PENGUJI EKSTERNAL - Alur Kerja

```mermaid
flowchart TD
    Start([Start: Menerima Undangan]) --> BukaEmail[Buka Email Undangan]
    BukaEmail --> KlikLink[Klik Link Login]
    
    KlikLink --> HalamanLogin[Halaman Login Eksternal]
    HalamanLogin --> InputNIP[Input NIP]
    InputNIP --> InputToken[Input Token dari Email]
    InputToken --> Login[Login]
    
    Login --> Dashboard[Dashboard Penguji Eksternal]
    
    Dashboard --> LihatJadwal[Lihat Jadwal Ujian]
    LihatJadwal --> CekWaktu{Cek Waktu}
    
    CekWaktu -->|Belum Waktunya| Tunggu[Tunggu Hari H]
    CekWaktu -->|Sudah Waktunya/Sudah Lewat| InputNilai[Input Nilai]
    
    Tunggu --> HariH[Hari H Ujian]
    HariH --> Hadir[Hadir di Lokasi/Online]
    Hadir --> IkutiUjian[Ikuti Proses Ujian]
    IkutiUjian --> TanyaJawab[Sesi Tanya Jawab]
    TanyaJawab --> Selesai[Ujian Selesai]
    Selesai --> InputNilai
    
    subgraph "Input Nilai"
        InputNilai --> IsiForm[Isi Form Penilaian]
        IsiForm --> NilaiPresentasi[Nilai Presentasi]
        NilaiPresentasi --> NilaiPemahaman[Nilai Pemahaman Materi]
        NilaiPemahaman --> NilaiPembelaan[Nilai Kemampuan Mempertahankan]
        NilaiPembelaan --> NilaiKualitas[Nilai Kualitas Skripsi]
        NilaiKualitas --> Rekomendasi{Rekomendasi}
        Rekomendasi -->|Lulus| Lulus[Lulus]
        Rekomendasi -->|Lulus dengan Revisi| LulusRevisi[Lulus dengan Revisi]
        Rekomendasi -->|Tidak Lulus| TidakLulus[Tidak Lulus]
        Lulus --> CatatanRevisi[Catatan Revisi]
        LulusRevisi --> CatatanRevisi
        TidakLulus --> CatatanRevisi
        CatatanRevisi --> Simpan[Simpan Nilai]
    end
    
    Simpan --> SelesaiTugas[Tugas Selesai]
    SelesaiTugas --> Logout[Logout]
    Logout --> End([End])
```

---

## 6. SUPERADMIN - Alur Kerja

```mermaid
flowchart TD
    Start([Start]) --> Login[Login ke Aplikasi]
    Login --> Dashboard[Dashboard Superadmin]
    
    Dashboard --> PilihMenu{Pilih Menu}
    
    PilihMenu -->|Manajemen User| ManajemenUser
    PilihMenu -->|Pengaturan Sistem| PengaturanSistem
    PilihMenu -->|Backup & Restore| BackupRestore
    PilihMenu -->|Laporan| Laporan
    PilihMenu -->|Monitoring| Monitoring
    
    subgraph "Manajemen User"
        ManajemenUser --> PilihAksi{Pilih Aksi}
        PilihAksi -->|Tambah User| TambahUser[Buat User Baru]
        PilihAksi -->|Edit User| EditUser[Edit User]
        PilihAksi -->|Reset Password| ResetPassword[Reset Password]
        PilihAksi -->|Hapus User| HapusUser[Hapus User]
        PilihAksi -->|Import| ImportUser[Import User dari CSV]
        PilihAksi -->|Kelola Role| KelolaRole[Kelola Role User]
        
        TambahUser --> IsiDataUser[Isi Data User]
        IsiDataUser --> SetRole[Set Role]
        SetRole --> SimpanUser[Simpan User]
        
        EditUser --> PilihUserEdit[Pilih User]
        PilihUserEdit --> UpdateData[Update Data]
        UpdateData --> SimpanEdit[Simpan Perubahan]
        
        ResetPassword --> PilihUserReset[Pilih User]
        PilihUserReset --> GeneratePassword[Generate Password Baru]
        GeneratePassword --> KirimPassword[Kirim Password ke User]
        
        HapusUser --> PilihUserHapus[Pilih User]
        PilihUserHapus --> KonfirmasiHapus{Konfirmasi Hapus}
        KonfirmasiHapus -->|Ya| DeleteUser[Hapus User]
        KonfirmasiHapus -->|Tidak| PilihAksi
        
        KelolaRole --> PilihUserRole[Pilih User]
        PilihUserRole --> TambahRole[Tambah Role]
        TambahRole --> HapusRole[Hapus Role]
        HapusRole --> SimpanRole[Simpan Role]
    end
    
    subgraph "Pengaturan Sistem"
        PengaturanSistem --> PilihPengaturan{Pilih Pengaturan}
        PilihPengaturan -->|Angkatan Aktif| AturAngkatan[Atur Angkatan Aktif]
        PilihPengaturan -->|Visibility Nilai| AturVisibility[Atur Visibility Nilai]
        PilihPengaturan -->|Backup| AturBackup[Atur Konfigurasi Backup]
        PilihPengaturan -->|Email| AturEmail[Atur Konfigurasi Email]
        
        AturAngkatan --> PilihAngkatan[Pilih Angkatan]
        PilihAngkatan --> SimpanAngkatan[Simpan Pengaturan]
        
        AturVisibility --> PilihTahap[Pilih Tahap]
        PilihTahap --> SetVisibility[Set Visibility]
        SetVisibility --> SimpanVisibility[Simpan Pengaturan]
        
        AturBackup --> SetPath[Set Path Backup]
        SetPath --> SetRetention[Set Retensi]
        SetRetention --> SetSchedule[Set Jadwal Backup]
        SetSchedule --> SimpanBackup[Simpan Pengaturan]
        
        AturEmail --> SetSMTP[Set Konfigurasi SMTP]
        SetSMTP --> TestEmail[Test Email]
        TestEmail --> SimpanEmail[Simpan Pengaturan]
    end
    
    subgraph "Backup & Restore"
        BackupRestore --> PilihAksiBackup{Pilih Aksi}
        PilihAksiBackup -->|Backup Manual| BackupManual[Backup Sekarang]
        PilihAksiBackup -->|Restore| RestoreDB[Restore dari Backup]
        PilihAksiBackup -->|Lihat History| LihatHistory[Lihat History Backup]
        
        BackupManual --> PilihDatabase[Pilih Database]
        PilihDatabase --> ExecuteBackup[Execute Backup]
        ExecuteBackup --> DownloadBackup[Download File Backup]
        
        RestoreDB --> UploadFile[Upload File Backup]
        UploadFile --> VerifyBackup[Verify Backup]
        VerifyBackup --> ExecuteRestore[Execute Restore]
        
        LihatHistory --> ListBackup[Daftar Backup]
        ListBackup --> DownloadBackup
        ListBackup --> DeleteBackup[Hapus Backup]
    end
    
    subgraph "Laporan"
        Laporan --> PilihJenisLaporan{Pilih Jenis}
        PilihJenisLaporan -->|User Activity| LaporanUser[Laporan Aktivitas User]
        PilihJenisLaporan -->|System| LaporanSystem[Laporan Sistem]
        PilihJenisLaporan -->|Audit Trail| LaporanAudit[Laporan Audit Trail]
        
        LaporanUser --> SetFilterUser[Set Filter]
        SetFilterUser --> GenerateUser[Generate Laporan]
        GenerateUser --> ExportUser[Export PDF/Excel]
        
        LaporanSystem --> CekHealth[Cek Health System]
        CekHealth --> GenerateSystem[Generate Laporan]
        GenerateSystem --> ExportSystem[Export PDF/Excel]
        
        LaporanAudit --> SetFilterAudit[Set Filter]
        SetFilterAudit --> GenerateAudit[Generate Laporan]
        GenerateAudit --> ExportAudit[Export PDF/Excel]
    end
    
    subgraph "Monitoring"
        Monitoring --> CekPerformance{Cek Performance}
        CekPerformance -->|Database| MonitorDB[Monitor Database]
        CekPerformance -->|Storage| MonitorStorage[Monitor Storage]
        CekPerformance -->|User Online| MonitorUser[Monitor User Online]
        
        MonitorDB --> CekConnection[Cek Koneksi]
        CekConnection --> CekQueryTime[Cek Query Time]
        
        MonitorStorage --> CekDiskSpace[Cek Disk Space]
        CekDiskSpace --> CekBackupSize[Cek Backup Size]
        
        MonitorUser --> ListOnline[Daftar User Online]
        ListOnline --> CekActivity[Cek Activity]
    end
    
    SimpanUser --> Dashboard
    SimpanEdit --> Dashboard
    KirimPassword --> Dashboard
    DeleteUser --> Dashboard
    SimpanRole --> Dashboard
    SimpanAngkatan --> Dashboard
    SimpanVisibility --> Dashboard
    SimpanBackup --> Dashboard
    SimpanEmail --> Dashboard
    DownloadBackup --> Dashboard
    ExecuteRestore --> Dashboard
    DeleteBackup --> Dashboard
    ExportUser --> Dashboard
    ExportSystem --> Dashboard
    ExportAudit --> Dashboard
    CekQueryTime --> Dashboard
    CekBackupSize --> Dashboard
    CekActivity --> Dashboard
    
    End([End])
```

---

## 7. ALUR KERJA BERSAMA (COLLABORATION)

### Diagram Kolaborasi - Proses Lengkap Skripsi

```mermaid
sequenceDiagram
    participant M as Mahasiswa
    participant K as Kombi
    participant DP as Dosen Pembimbing
    participant P as Dosen Penguji
    participant E as Penguji Eksternal
    participant S as Sistem
    
    M->>S: Login
    K->>S: Login
    DP->>S: Login
    P->>S: Login
    
    Note over M,S: Tahap 1: Pengajuan Judul
    M->>S: Ajukan Judul
    S->>K: Notifikasi: Judul Baru
    K->>S: Review Judul
    K->>S: Terima/Tolak Judul
    S->>M: Notifikasi: Hasil Verifikasi
    
    Note over K,S: Tahap 2: Penugasan Dosen
    K->>S: Set Pembimbing & Penguji
    S->>DP: Notifikasi: Penugasan Pembimbing
    S->>P: Notifikasi: Penugasan Penguji
    
    Note over M,DP: Tahap 3: Proses Bimbingan
    M->>DP: Konsultasi
    DP->>M: Feedback
    
    Note over K,S: Tahap 4: Jadwal Seminar
    K->>S: Buat Jadwal Sempro
    S->>M: Notifikasi: Jadwal
    S->>DP: Notifikasi: Jadwal
    S->>P: Notifikasi: Jadwal
    K->>E: Undang Penguji Eksternal
    E->>S: Login dengan Token
    
    Note over M,P,E: Tahap 5: Hari H Seminar
    M->>S: Hadir Seminar
    DP->>S: Hadir Seminar
    P->>S: Hadir Seminar
    E->>S: Hadir Seminar
    
    Note over DP,P,E: Tahap 6: Input Nilai
    DP->>S: Input Nilai
    P->>S: Input Nilai
    E->>S: Input Nilai
    
    Note over K,S: Tahap 7: Verifikasi Nilai
    S->>K: Notifikasi: Nilai Masuk
    K->>S: Review & Approve Nilai
    
    Note over M,S: Tahap 8: Mahasiswa Lihat Hasil
    S->>M: Notifikasi: Nilai Terverifikasi
    M->>S: Lihat Timeline & Nilai
    
    Note over M,K,S: Tahap 9-11: Ulangi untuk Semhas, Pra-Ujian, Ujian
    
    Note over K,S: Tahap 12: Kelulusan
    K->>S: Hitung Nilai Akhir
    K->>S: Tentukan Kelulusan
    S->>M: Notifikasi: Status Kelulusan
    
    Note over M,S: Tahap 13: Selesai
    M->>S: Download Nilai & Laporan
```

---

## Legenda BPMN

| Simbol BPMN | Deskripsi | Penggunaan di Dokumen Ini |
|-------------|-----------|---------------------------|
| ⚪ Circle | Start Event | Memulai proses |
| ⬛ Rectangle | Task/Activity | Aktivitas yang dilakukan user |
| ◇ Diamond | Gateway (XOR) | Keputusan ya/tidak |
| ⬠ Rounded Rectangle | Sub-process | Proses yang memiliki detail tersendiri |
| ⚫ Circle with thick border | End Event | Mengakhiri proses |
| → Arrow | Sequence Flow | Alur proses |
| --.→ Dashed Arrow | Message Flow | Komunikasi antar user |
| Note | Annotation | Catatan tambahan |

---

## Catatan Penting

1. **BPMN dalam Markdown**: Diagram di atas menggunakan sintaks Mermaid yang dapat dirender di GitHub, GitLab, dan platform Markdown lainnya.

2. **Simplifikasi**: Beberapa elemen BPMN disederhanakan untuk kemudahan pemahaman. Untuk implementasi BPMN yang lebih detail, dapat menggunakan tools seperti Camunda, Bizagi, atau Signavio.

3. **Ekstensi**: File ini dapat dikonversi ke format BPMN standar (.bpmn) menggunakan tools seperti Mermaid CLI atau draw.io.

4. **Customization**: Setiap institusi dapat menyesuaikan alur kerja sesuai dengan kebijakan internal masing-masing.

---

*Dokumentasi BPMN ini disusun untuk memvisualisasikan alur kerja aplikasi KBS secara standar dan mudah dipahami.*
