<?php
$presetType = '';
if (isset($_GET['type']) && in_array($_GET['type'], ['mahasiswa', 'dosen'], true)) {
    $presetType = $_GET['type'];
}
$presetUserId = 0;
if (isset($_GET['user_id']) && ctype_digit((string)$_GET['user_id'])) {
    $presetUserId = (int)$_GET['user_id'];
}
?>

<div class="row">
    <div class="col-12">
        <h2>Reset Password Pengguna</h2>
        <p>Reset password untuk mahasiswa dan dosen</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Reset Password</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <h5><?= htmlspecialchars($success) ?></h5>
                    <p>Pengguna: <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['username']) ?>)</p>
                    <p>Password baru: <strong><?= htmlspecialchars($new_password) ?></strong></p>
                    <p class="text-muted">Harap beritahu pengguna untuk segera mengganti password setelah login.</p>
                </div>
                <?php endif; ?>
                
                <form method="POST"
                      action="/users/reset-password"
                      id="resetPasswordForm"
                      data-preset-type="<?= htmlspecialchars($presetType, ENT_QUOTES, 'UTF-8') ?>"
                      data-preset-user-id="<?= $presetUserId > 0 ? (int)$presetUserId : '' ?>">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="user_type" class="form-label">Jenis Pengguna</label>
                        <select class="form-select" id="user_type" name="user_type" required>
                            <option value="">Pilih Jenis Pengguna</option>
                            <option value="mahasiswa" <?= $presetType === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                            <option value="dosen" <?= $presetType === 'dosen' ? 'selected' : '' ?>>Dosen</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Pengguna</label>
                        <select class="form-select" id="user_id" name="user_id" required disabled>
                            <option value="">Pilih Jenis Pengguna terlebih dahulu</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning" id="resetButton" disabled>Reset Password</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Informasi</h5>
            </div>
            <div class="card-body">
                <h6>Password Default</h6>
                <ul>
                    <li>Mahasiswa: <strong>NIM mahasiswa (sama dengan username)</strong></li>
                    <li>Dosen: <strong>NIP dosen (sama dengan username)</strong></li>
                </ul>
                
                <h6>Catatan</h6>
                <ul>
                    <li>Setelah reset password, pengguna login dengan password default dan sistem akan meminta penggantian password</li>
                    <li>Pastikan hanya Kombi dan Superadmin yang dapat mengakses fitur ini</li>
                    <li>Password default bersifat sementara dan harus segera diganti</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeSelect = document.getElementById('user_type');
    const userIdSelect = document.getElementById('user_id');
    const resetButton = document.getElementById('resetButton');
    const form = document.getElementById('resetPasswordForm');
    if (!form) {
        return;
    }

    const presetType = form.dataset.presetType || '';
    let pendingPresetUserId = form.dataset.presetUserId || '';
    
    userTypeSelect.addEventListener('change', function() {
        const userType = this.value;
        
        if (userType === '') {
            userIdSelect.innerHTML = '<option value="">Pilih Jenis Pengguna terlebih dahulu</option>';
            userIdSelect.disabled = true;
            resetButton.disabled = true;
            pendingPresetUserId = '';
            return;
        }
        
        userIdSelect.disabled = true;
        resetButton.disabled = true;
        userIdSelect.innerHTML = '<option value="">Memuat data...</option>';

        const preselectForType = (presetType === userType) ? pendingPresetUserId : '';

        // Fetch users by type
        fetch(`/users/get-users?type=${userType}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                // Clear and populate user select
                userIdSelect.innerHTML = '<option value="">Pilih Pengguna</option>';
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.username})`;
                    userIdSelect.appendChild(option);
                });
                
                userIdSelect.disabled = false;

                if (preselectForType) {
                    userIdSelect.value = preselectForType;
                    if (userIdSelect.value === preselectForType) {
                        resetButton.disabled = false;
                        pendingPresetUserId = '';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data pengguna');
            });
    });
    
    userIdSelect.addEventListener('change', function() {
        resetButton.disabled = this.value === '';
    });

    if (presetType) {
        userTypeSelect.value = presetType;
        userTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
