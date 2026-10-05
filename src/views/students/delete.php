<div class="row">
    <div class="col-12">
        <h2>Hapus Mahasiswa</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="alert alert-warning">
                    <h5>Apakah Anda yakin ingin menghapus mahasiswa ini?</h5>
                    <p><strong>NIM:</strong> <?= htmlspecialchars($student->nim) ?></p>
                    <p><strong>Nama:</strong> <?= htmlspecialchars($student->name) ?></p>
                    <p>Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                
                <form method="POST" action="/students/<?= $student->id ?>/delete">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        <a href="/students/<?= $student->id ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
