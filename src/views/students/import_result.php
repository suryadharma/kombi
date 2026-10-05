<div class="row">
    <div class="col-12">
        <h2>Hasil Impor Data Mahasiswa</h2>
        <p>Ringkasan proses impor data mahasiswa</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Ringkasan Impor</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h5>Data Berhasil Diimpor</h5>
                    <p><?= $importedCount ?> data mahasiswa berhasil diimpor ke dalam sistem.</p>
                </div>
                
                <?php if (isset($duplicateCount) && $duplicateCount > 0): ?>
                <div class="alert alert-warning">
                    <h5>Data Duplikat</h5>
                    <p><?= $duplicateCount ?> data mahasiswa tidak diimpor karena NIM sudah digunakan.</p>
                    <?php if (isset($duplicates) && !empty($duplicates)): ?>
                    <p>NIM yang sudah ada: <?= implode(', ', array_slice($duplicates, 0, 10)) ?><?= count($duplicates) > 10 ? ' dan ' . (count($duplicates) - 10) . ' lainnya' : '' ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5>Error yang Terjadi</h5>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <a href="/students" class="btn btn-primary">Lihat Daftar Mahasiswa</a>
                <a href="/students/import" class="btn btn-secondary">Impor Lagi</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Petunjuk Selanjutnya</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Mahasiswa dapat login menggunakan NIM sebagai username</li>
                    <li>Password default sama dengan NIM</li>
                    <li>Mahasiswa disarankan mengganti password setelah login pertama</li>
                    <li>Periksa kembali data yang diimpor untuk memastikan keakuratan informasi</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Panduan untuk Mahasiswa</h5>
            </div>
            <div class="card-body">
                <h6>Langkah Login untuk Mahasiswa</h6>
                <ol>
                    <li>Buka halaman login aplikasi</li>
                    <li>Masukkan username: NIM masing-masing</li>
                    <li>Masukkan password: NIM (default)</li>
                    <li>Klik tombol login</li>
                    <li>Sistem akan meminta untuk mengganti password default</li>
                    <li>Masukkan password saat ini (NIM)</li>
                    <li>Masukkan password baru dan konfirmasi password baru</li>
                    <li>Klik tombol "Ubah Password"</li>
                </ol>
                
                <h6>Catatan Penting</h6>
                <ul>
                    <li>Mahasiswa tidak dapat melihat nilai dalam sistem ini</li>
                    <li>Mahasiswa hanya dapat mengajukan judul skripsi</li>
                    <li>Status mahasiswa dapat dilihat dalam timeline</li>
                </ul>
            </div>
        </div>
    </div>
</div>