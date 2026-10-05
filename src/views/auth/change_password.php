<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Ubah Password</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['should_change_password'])): ?>
                <div class="alert alert-info">
                    <strong>Info!</strong> Anda login dengan password default. Disarankan untuk mengubah password Anda.
                </div>
                <?php endif; ?>
                
                <form method="POST" action="/auth/change-password">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                        <a href="/dashboard" class="btn btn-secondary">Nanti</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>