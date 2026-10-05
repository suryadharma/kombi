<?php
$title = $title ?? 'Form Pengguna';
$formAction = $formAction ?? '/users/store';
$roles = $roles ?? [];
$formData = $formData ?? [
    'username' => '',
    'name' => '',
    'role' => 'kombi',
];
$errors = $errors ?? [];
?>

<div class="mb-3">
    <a href="/users" class="btn btn-link ps-0">
        <i class="fas fa-arrow-left"></i> Kembali ke daftar pengguna
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= htmlspecialchars($formAction) ?>">
            <?= Csrf::field(); ?>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text"
                       class="form-control"
                       id="username"
                       name="username"
                       value="<?= htmlspecialchars($formData['username'] ?? '') ?>"
                       required>
                <div class="form-text">
                    Username harus unik. Password default akan mengikuti username jika tidak diisi.
                </div>
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text"
                       class="form-control"
                       id="name"
                       name="name"
                       value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                       required>
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <?php foreach ($roles as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= ($formData['role'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           autocomplete="new-password">
                    <div class="form-text">
                        Kosongkan bila tidak ingin mengubah password.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input type="password"
                           class="form-control"
                           id="password_confirmation"
                           name="password_confirmation"
                           autocomplete="new-password">
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="/users" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
