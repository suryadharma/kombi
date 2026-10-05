<div class="row">
    <div class="col-12">
        <h2>Impor Data Mahasiswa</h2>
        <p>Impor data mahasiswa dari file CSV</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Impor</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="/students/import/process" enctype="multipart/form-data">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="student_file" class="form-label">File CSV</label>
                        <input type="file" class="form-control" id="student_file" name="student_file" accept=".csv,text/csv" required>
                        <div class="form-text">File harus berformat CSV dengan kolom: NIM, Nama, Angkatan, Semester Masuk, Status (opsional)</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="generate_accounts" name="generate_accounts" checked>
                            <label class="form-check-label" for="generate_accounts">
                                Buat akun login otomatis (username=NIM, password=default=NIM)
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Impor Data</button>
                    <a href="/students" class="btn btn-secondary">Kembali ke Daftar</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Contoh Format CSV</h5>
            </div>
            <div class="card-body">
                <pre>
NIM,Nama,Angkatan,Semester Masuk,Status
23241001,Andi Susanto,2023,23241,AKTIF
23241002,Budi Prasetyo,2023,23241,AKTIF
23241003,Citra Dewi,2023,23241,CUTI
                </pre>
                
                <h6>Penjelasan Format:</h6>
                <ul>
                    <li><strong>NIM</strong>: Nomor Induk Mahasiswa</li>
                    <li><strong>Nama</strong>: Nama lengkap mahasiswa</li>
                    <li><strong>Angkatan</strong>: Tahun angkatan (misal: 2023)</li>
                    <li><strong>Semester Masuk</strong>: Format 23241 (2023/2024 Ganjil) atau 2324 (2023/2024 Ganjil)</li>
                    <li><strong>Status</strong>: AKTIF, CUTI, NON-AKTIF, LULUS (opsional, default AKTIF)</li>
                </ul>
                
                <h6>Format Semester Masuk</h6>
                <ul>
                    <li><strong>23241</strong> untuk tahun ajaran 2023/2024 semester ganjil</li>
                    <li><strong>23242</strong> untuk tahun ajaran 2023/2024 semester genap</li>
                    <li><strong>2324</strong> untuk tahun ajaran 2023/2024 semester ganjil (format alternatif)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Panduan Impor Data Mahasiswa</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li>Persiapkan file CSV dengan format yang sesuai seperti contoh di samping</li>
                    <li>Kolom wajib: NIM, Nama, Angkatan, Semester Masuk</li>
                    <li>Kolom Status bersifat opsional, default adalah "AKTIF"</li>
                    <li>Klik tombol "Pilih File" dan pilih file CSV yang telah disiapkan</li>
                    <li>Pastikan opsi "Buat akun login otomatis" dicentang jika ingin membuat akun untuk mahasiswa</li>
                    <li>Klik tombol "Impor Data" untuk memulai proses impor</li>
                    <li>Tunggu hingga proses selesai dan periksa hasilnya</li>
                </ol>
                
                <h6>Informasi Akun Mahasiswa</h6>
                <ul>
                    <li>Username: NIM mahasiswa</li>
                    <li>Password default: Sama dengan NIM</li>
                    <li>Mahasiswa harus mengganti password saat login pertama kali</li>
                </ul>
            </div>
        </div>
    </div>
</div>
