<?php
$settings = $settings ?? [];
$currentSettings = [
    'backup_enabled' => $settings['backup_enabled'] ?? true,
    'backup_type' => $settings['backup_type'] ?? 'local',
    'retention_days' => $settings['retention_days'] ?? 30,
    'smb_enabled' => $settings['smb_enabled'] ?? false,
    'smb_host' => $settings['smb_host'] ?? '',
    'smb_share' => $settings['smb_share'] ?? '',
    'smb_path' => $settings['smb_path'] ?? '',
    'smb_username' => $settings['smb_username'] ?? '',
    'smb_workgroup' => $settings['smb_workgroup'] ?? '',
    'smb_binary' => $settings['smb_binary'] ?? '/usr/bin/smbclient',
    'ftp_enabled' => $settings['ftp_enabled'] ?? false,
    'ftp_host' => $settings['ftp_host'] ?? '',
    'ftp_port' => $settings['ftp_port'] ?? 21,
    'ftp_username' => $settings['ftp_username'] ?? '',
    'ftp_path' => $settings['ftp_path'] ?? '',
    'ftp_passive' => $settings['ftp_passive'] ?? true,
];
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server me-2"></i>
                    Pengaturan Backup
                </h5>
            </div>
            <div class="card-body">
                <form id="backupSettingsForm" method="POST" action="/settings/backup" class="needs-validation">
                    <?= csrf_field() ?>

                    <!-- Pengaturan Umum -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="card-title mb-3">Pengaturan Umum</h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="backup_enabled" name="backup_enabled" 
                                       value="1" <?= $currentSettings['backup_enabled'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="backup_enabled">
                                    <strong>Aktifkan Backup Otomatis</strong>
                                    <div class="form-text">Backup akan dibuat otomatis sesuai jadwal</div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="retention_days" class="form-label">Retensi Backup (Hari)</label>
                                <input type="number" class="form-control" id="retention_days" name="retention_days" 
                                       min="1" max="365" value="<?= htmlspecialchars($currentSettings['retention_days']) ?>">
                                <small class="text-muted">Backup yang lebih lama dari jumlah hari ini akan dihapus otomatis.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="card-title mb-3">Tipe Backup</h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check form-check-inline mb-3">
                                <input type="radio" class="form-check-input" id="backup_type_local" name="backup_type" value="local" 
                                       <?= $currentSettings['backup_type'] === 'local' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="backup_type_local">
                                    <i class="fas fa-hdd me-2"></i> Local (Hanya Lokal)
                                </label>

                                <input type="radio" class="form-check-input" id="backup_type_smb" name="backup_type" value="smb" 
                                       <?= $currentSettings['backup_type'] === 'smb' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="backup_type_smb">
                                    <i class="fas fa-share-nodes me-2"></i> SMB (Windows Share)
                                </label>

                                <input type="radio" class="form-check-input" id="backup_type_ftp" name="backup_type" value="ftp" 
                                       <?= $currentSettings['backup_type'] === 'ftp' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="backup_type_ftp">
                                    <i class="fas fa-cloud me-2"></i> FTP Server
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan SMB -->
                    <div id="smb-settings" class="mb-4" style="display: <?= $currentSettings['backup_type'] === 'smb' ? 'block' : 'none'; ?>">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-share-nodes me-2"></i>
                                    Pengaturan SMB
                                    <div class="form-check form-switch float-end">
                                        <input class="form-check-input" type="checkbox" id="smb_enabled" name="smb_enabled" 
                                               value="1" <?= $currentSettings['smb_enabled'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="smb_enabled">Aktif</label>
                                    </div>
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smb_host" class="form-label">Host SMB</label>
                                <input type="text" class="form-control" id="smb_host" name="smb_host" 
                                       value="<?= htmlspecialchars($currentSettings['smb_host']) ?>" 
                                       placeholder="192.168.100.145">
                            </div>
                            <div class="col-md-6">
                                <label for="smb_share" class="form-label">Nama Share</label>
                                <input type="text" class="form-control" id="smb_share" name="smb_share" 
                                       value="<?= htmlspecialchars($currentSettings['smb_share']) ?>" 
                                       placeholder="backup">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smb_path" class="form-label">Path di Share</label>
                                <input type="text" class="form-control" id="smb_path" name="smb_path" 
                                       value="<?= htmlspecialchars($currentSettings['smb_path']) ?>" 
                                       placeholder="/kombi">
                            </div>
                            <div class="col-md-6">
                                <label for="smb_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="smb_username" name="smb_username" 
                                       value="<?= htmlspecialchars($currentSettings['smb_username']) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smb_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="smb_password" name="smb_password" 
                                       value="********" placeholder="Kosongkan jika tidak berubah">
                            </div>
                            <div class="col-md-6">
                                <label for="smb_workgroup" class="form-label">Workgroup/Domain</label>
                                <input type="text" class="form-control" id="smb_workgroup" name="smb_workgroup" 
                                       value="<?= htmlspecialchars($currentSettings['smb_workgroup']) ?>" 
                                       placeholder="WORKGROUP">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="smb_binary" class="form-label">Path smbclient</label>
                                <input type="text" class="form-control" id="smb_binary" name="smb_binary" 
                                       value="<?= htmlspecialchars($currentSettings['smb_binary']) ?>" 
                                       placeholder="/usr/bin/smbclient">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-outline-primary" onclick="testSmbConnection()">
                                    <i class="fas fa-plug me-2"></i> Test Koneksi SMB
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan FTP -->
                    <div id="ftp-settings" class="mb-4" style="display: <?= $currentSettings['backup_type'] === 'ftp' ? 'block' : 'none'; ?>">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-cloud me-2"></i>
                                    Pengaturan FTP
                                    <div class="form-check form-switch float-end">
                                        <input class="form-check-input" type="checkbox" id="ftp_enabled" name="ftp_enabled" 
                                               value="1" <?= $currentSettings['ftp_enabled'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="ftp_enabled">Aktif</label>
                                    </div>
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ftp_host" class="form-label">Host FTP</label>
                                <input type="text" class="form-control" id="ftp_host" name="ftp_host" 
                                       value="<?= htmlspecialchars($currentSettings['ftp_host']) ?>" 
                                       placeholder="ftp.example.com">
                            </div>
                            <div class="col-md-6">
                                <label for="ftp_port" class="form-label">Port</label>
                                <input type="number" class="form-control" id="ftp_port" name="ftp_port" 
                                       min="1" max="65535" value="<?= htmlspecialchars($currentSettings['ftp_port']) ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ftp_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="ftp_username" name="ftp_username" 
                                       value="<?= htmlspecialchars($currentSettings['ftp_username']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="ftp_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="ftp_password" name="ftp_password" 
                                       value="********" placeholder="Kosongkan jika tidak berubah">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ftp_path" class="form-label">Path di Server</label>
                                <input type="text" class="form-control" id="ftp_path" name="ftp_path" 
                                       value="<?= htmlspecialchars($currentSettings['ftp_path']) ?>" 
                                       placeholder="/backup">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="ftp_passive" name="ftp_passive" 
                                           value="1" <?= $currentSettings['ftp_passive'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ftp_passive">Mode Passive</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-outline-primary" onclick="testFtpConnection()">
                                    <i class="fas fa-plug me-2"></i> Test Koneksi FTP
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                            <a href="/settings" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Batal
                            </a>
                        </div>
                    </div>

                    <!-- Pesan Notifikasi -->
                    <div id="test-result" class="alert" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function testSmbConnection() {
    const form = document.getElementById('backupSettingsForm');
    const formData = new FormData(form);
    
    // Remove password from form data for security
    formData.delete('smb_password');
    
    fetch('/settings/backup/test-smb', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('test-result');
        resultDiv.style.display = 'block';
        
        if (data.success) {
            resultDiv.className = 'alert alert-success';
            resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
        } else {
            resultDiv.className = 'alert alert-danger';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
        }
        
        setTimeout(() => {
            resultDiv.style.display = 'none';
        }, 5000);
    })
    .catch(error => {
        console.error('Error:', error);
        const resultDiv = document.getElementById('test-result');
        resultDiv.style.display = 'block';
        resultDiv.className = 'alert alert-danger';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + error.message;
    });
}

function testFtpConnection() {
    const form = document.getElementById('backupSettingsForm');
    const formData = new FormData(form);
    
    // Remove password from form data for security
    formData.delete('ftp_password');
    
    fetch('/settings/backup/test-ftp', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('test-result');
        resultDiv.style.display = 'block';
        
        if (data.success) {
            resultDiv.className = 'alert alert-success';
            resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
        } else {
            resultDiv.className = 'alert alert-danger';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
        }
        
        setTimeout(() => {
            resultDiv.style.display = 'none';
        }, 5000);
    })
    .catch(error => {
        console.error('Error:', error);
        const resultDiv = document.getElementById('test-result');
        resultDiv.style.display = 'block';
        resultDiv.className = 'alert alert-danger';
        resultDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error: ' + error.message;
    });
}

// Show/hide SMB settings based on backup type
document.addEventListener('DOMContentLoaded', function() {
    const backupTypeRadios = document.querySelectorAll('input[name="backup_type"]');
    
    backupTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const smbSettings = document.getElementById('smb-settings');
            const ftpSettings = document.getElementById('ftp-settings');
            
            if (this.value === 'smb') {
                smbSettings.style.display = 'block';
                ftpSettings.style.display = 'none';
            } else if (this.value === 'ftp') {
                smbSettings.style.display = 'none';
                ftpSettings.style.display = 'block';
            } else {
                smbSettings.style.display = 'none';
                ftpSettings.style.display = 'none';
            }
        });
    });
    
    // Trigger initial state
    const checkedRadio = document.querySelector('input[name="backup_type"]:checked');
    if (checkedRadio) {
        checkedRadio.dispatchEvent(new Event('change'));
    }
});
</script>
