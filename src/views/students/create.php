<div class="row">
    <div class="col-12">
        <h2>Tambah Mahasiswa</h2>
        <p>Tambahkan data mahasiswa baru</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Mahasiswa</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="/students/store">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" class="form-control" id="nim" name="nim" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="angkatan" class="form-label">Angkatan</label>
                        <input type="number" class="form-control" id="angkatan" name="angkatan" min="1900" max="2100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="semester_masuk" class="form-label">Semester Masuk</label>
                        <input type="text" class="form-control" id="semester_masuk" name="semester_masuk" required>
                        <div class="form-text">
                            Format: 23241 (2023/2024 Ganjil), 23242 (2023/2024 Genap), atau 2324 (2023/2024 Ganjil)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="AKTIF">AKTIF</option>
                            <option value="CUTI">CUTI</option>
                            <option value="NON-AKTIF">NON-AKTIF</option>
                            <option value="MENGULANG">MENGULANG</option>
                            <option value="LULUS">LULUS</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="/students" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Panduan</h5>
            </div>
            <div class="card-body">
                <h6>Format Semester Masuk</h6>
                <p>Gunakan format berikut untuk kolom "Semester Masuk":</p>
                <ul>
                    <li><strong>23241</strong> untuk tahun ajaran 2023/2024 semester ganjil</li>
                    <li><strong>23242</strong> untuk tahun ajaran 2023/2024 semester genap</li>
                    <li><strong>2324</strong> untuk tahun ajaran 2023/2024 semester ganjil (format alternatif)</li>
                </ul>
                
                <h6>Akun Login</h6>
                <p>Setelah menyimpan data mahasiswa:</p>
                <ul>
                    <li>Username: NIM mahasiswa</li>
                    <li>Password default: Sama dengan NIM</li>
                    <li>Mahasiswa harus mengganti password saat login pertama kali</li>
                </ul>
            </div>
        </div>
    </div>
</div>
