<div class="row">
    <div class="col-12">
        <h2>Tambah Dosen</h2>
        <p>Tambahkan data dosen baru</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Dosen</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="/lecturers/store">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" class="form-control" id="nip" name="nip" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="prodi" class="form-label">Prodi</label>
                        <input type="text" class="form-control" id="prodi" name="prodi">
                        <div class="form-text">Kosongkan jika dosen eksternal</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                        <div class="form-text">Email untuk notifikasi pengiriman PDF hasil penilaian</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_external" name="is_external">
                        <label class="form-check-label" for="is_external">Dosen Eksternal</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="/lecturers" class="btn btn-secondary">Kembali</a>
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
                <h6>Informasi</h6>
                <ul>
                    <li>NIP: Nomor Induk Pegawai dosen</li>
                    <li>Nama: Nama lengkap dosen</li>
                    <li>Prodi: Program studi tempat dosen mengajar</li>
                    <li>Email: Email untuk menerima PDF hasil penilaian (opsional)</li>
                    <li>Dosen Eksternal: Centang jika dosen berasal dari luar institusi</li>
                </ul>
                
                <h6>Akun Login</h6>
                <ul>
                    <li>Untuk dosen internal, sistem akan membuat akun login otomatis</li>
                    <li>Username: NIP dosen</li>
                    <li>Password default: Sama dengan NIP</li>
                </ul>
            </div>
        </div>
    </div>
</div>
