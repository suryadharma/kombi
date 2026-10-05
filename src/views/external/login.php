<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="text-center mb-0">Login Penguji Eksternal</h3>
                <a href="/login" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="POST" action="/external/login">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="nip" class="form-label">NIP/NIDN</label>
                        <input type="text" class="form-control" id="nip" name="nip" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="token" class="form-label">Token</label>
                        <input type="text" class="form-control" id="token" name="token" required>
                        <div class="form-text">Token yang diterima melalui undangan</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="nda" name="nda" required>
                            <label class="form-check-label" for="nda">
                                Saya menyetujui NDA ringkas dan akan menjaga kerahasiaan informasi
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p class="text-muted">Belum menerima undangan? Hubungi Kombi.</p>
                </div>
            </div>
        </div>
    </div>
</div>
