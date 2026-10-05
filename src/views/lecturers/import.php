<div class="row">
    <div class="col-12">
        <h2>Impor Data Dosen</h2>
        <p>Impor data dosen dari file CSV</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <a href="/lecturers" class="btn btn-secondary">Kembali ke Daftar Dosen</a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-warning">
        <h5>Error selama proses impor:</h5>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Import Dosen</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/lecturers/import/process" enctype="multipart/form-data">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">File CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        <div class="form-text">File harus dalam format CSV dengan kolom: NIP, Nama, Prodi, External (opsional)</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Impor Data</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Panduan Impor Dosen</h5>
            </div>
            <div class="card-body">
                <h6>Cara Mengimpor Data Dosen dari File CSV</h6>
                <ol>
                    <li>Klik tombol <strong>"Kembali ke Daftar Dosen"</strong> di atas</li>
                    <li>Siapkan file CSV dengan format berikut:
                        <pre>
NIP,Nama,Prodi,External
123456789,Dr. Budi Santoso,TI,Ya
987654321,Prof. Ani Wijaya,SI,Tidak</pre>
                    </li>
                    <li>Kolom yang wajib diisi: NIP, Nama, Prodi</li>
                    <li>Kolom External bersifat opsional, isi dengan "Ya"/"True"/"1" untuk dosen eksternal, atau kosongkan untuk dosen internal</li>
                    <li>Untuk dosen internal, sistem akan secara otomatis membuat akun pengguna dengan:
                        <ul>
                            <li>Username: NIP dosen</li>
                            <li>Password default: Sama dengan NIP</li>
                        </ul>
                    </li>
                    <li>Dosen harus mengganti password saat login pertama kali</li>
                    <li>Pilih file dan klik "Impor Data"</li>
                </ol>
            </div>
        </div>
    </div>
</div>