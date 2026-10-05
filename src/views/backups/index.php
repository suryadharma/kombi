<?php
$backups = $backups ?? [];
$success = $success ?? null;
$error = $error ?? null;
$storagePath = $storagePath ?? '';
$externalTarget = $externalTarget ?? null;
?>

<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-0"><i class="fas fa-database me-2"></i>Backup &amp; Restore</h2>
            <p class="text-muted mb-0 small">Kelola cadangan database dan berkas unggahan KomBi.</p>
        </div>
        <form action="/backups/create" method="POST" class="d-inline-block">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-download-alt me-1"></i> Backup Sekarang
            </button>
        </form>
        <form action="/backups/sync" method="POST" class="d-inline-block">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i> Sinkronkan ke SMB
            </button>
        </form>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success mt-3">
    <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger mt-3">
    <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="alert alert-warning mt-3">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center">
        <div class="me-lg-3 mb-2 mb-lg-0">
            <i class="fas fa-shield-alt fa-2x text-warning"></i>
        </div>
        <div>
            <strong>Catatan penting:</strong>
            <ul class="mb-0">
                <li>Backup berisi database dan folder unggahan (<code>public/uploads</code>).</li>
                <li>Restore akan menimpa data sekarang. Pastikan tidak ada pengguna lain yang aktif.</li>
                <li>Simpen salinan cadangan di lokasi lain sebelum melakukan restore.</li>
            </ul>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Daftar Cadangan</h5>
            <span class="text-muted small">Direktori lokal: <code><?= htmlspecialchars($storagePath) ?></code></span>
            <?php if ($externalTarget): ?>
            <div class="text-muted small">Mirror eksternal (opsional): <code><?= htmlspecialchars($externalTarget) ?></code></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($backups)): ?>
        <div class="p-4 text-center text-muted">
            Belum ada cadangan yang tersimpan.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Ukuran</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($backup['name']) ?></code></td>
                        <td><?= date('d M Y H:i:s', $backup['modified_at']) ?></td>
                        <td><?= number_format($backup['size'] / 1024 / 1024, 2) ?> MB</td>
                        <td class="text-end">
                            <a href="/backups/download/<?= urlencode($backup['name']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="/backups/restore" method="POST" class="d-inline-block" onsubmit="return confirm('Restore akan menimpa data sekarang. Lanjutkan?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['name']) ?>">
                                <input type="hidden" name="restore_uploads" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-history"></i>
                                </button>
                            </form>
                            <form action="/backups/delete" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus cadangan ini secara permanen?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['name']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-white">
        <h5 class="mb-0">Restore Dari Cadangan</h5>
    </div>
    <div class="card-body">
        <!-- Source Selection Tabs -->
        <ul class="nav nav-tabs mb-3" id="restoreSourceTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="local-tab" data-bs-toggle="tab" data-bs-target="#local" type="button" role="tab">
                    <i class="fas fa-hdd me-1"></i> Dari Server Lokal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Upload File
                </button>
            </li>
        </ul>

        <div class="tab-content" id="restoreSourceTabContent">
            <!-- Local Backup Tab -->
            <div class="tab-pane fade show active" id="local" role="tabpanel">
                <?php if (!empty($backups)): ?>
                <form action="/backups/restore" method="POST" id="localRestoreForm">
                    <?= Csrf::field() ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="backup_file" class="form-label">Pilih cadangan</label>
                            <select class="form-select" id="backup_file" name="backup_file" required>
                                <option value="">-- pilih cadangan --</option>
                                <?php foreach ($backups as $backup): ?>
                                <option value="<?= htmlspecialchars($backup['name']) ?>">
                                    <?= htmlspecialchars($backup['name']) ?> (<?= date('d M Y H:i', $backup['modified_at']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" value="1" id="restore_uploads" name="restore_uploads" checked>
                                <label class="form-check-label" for="restore_uploads">
                                    Pulihkan folder uploads
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button type="submit" class="btn btn-danger mt-3 mt-md-0" id="localRestoreBtn" disabled>
                                <i class="fas fa-undo-alt me-1"></i> Restore
                            </button>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>Belum ada cadangan yang tersimpan di server lokal.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Upload File Tab -->
            <div class="tab-pane fade" id="upload" role="tabpanel">
                <form action="/backups/restore" method="POST" enctype="multipart/form-data" id="uploadRestoreForm">
                    <?= Csrf::field() ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="backup_upload" class="form-label">
                                <i class="fas fa-file-archive me-1"></i> Upload File Backup
                            </label>
                            <input type="file" class="form-control" id="backup_upload" name="backup_upload" accept=".tar.gz,.tgz" required>
                            <div class="form-text">Format yang didukung: .tar.gz atau .tgz (maksimal 100 MB)</div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" value="1" id="restore_uploads_upload" name="restore_uploads" checked>
                                <label class="form-check-label" for="restore_uploads_upload">
                                    Pulihkan folder uploads
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button type="submit" class="btn btn-primary mt-3 mt-md-0" id="uploadRestoreBtn" disabled>
                                <i class="fas fa-upload me-1"></i> Upload & Restore
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Warning -->
        <div class="alert alert-danger mt-3 mb-0">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-2">⚠️ PERINGATAN: TINDAKAN BERBAHAYA!</h6>
                    <p class="mb-2 small">Restore akan <strong>MENIMPA SELURUH DATA</strong> yang ada sekarang:</p>
                    <ul class="mb-3 small">
                        <li>✗ Semua data database akan diganti</li>
                        <li>✗ Folder uploads akan ditimpa (jika dicentang)</li>
                        <li>✗ Perubahan sejak backup terakhir akan <strong>HILANG PERMANEN</strong></li>
                        <li>✗ Tindakan <strong>TIDAK DAPAT dibatalkan</strong></li>
                    </ul>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="restoreConfirmCheck" onchange="toggleRestoreButtons()">
                        <label class="form-check-label fw-bold" for="restoreConfirmCheck">
                            Saya memahami risiko di atas dan ingin melanjutkan restore
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRestoreButtons() {
    const checked = document.getElementById('restoreConfirmCheck').checked;
    document.getElementById('localRestoreBtn').disabled = !checked;
    document.getElementById('uploadRestoreBtn').disabled = !checked;
}

// Handle local restore form submission
document.getElementById('localRestoreForm').addEventListener('submit', function(e) {
    const backupFile = document.getElementById('backup_file').value;
    const confirmCheck = document.getElementById('restoreConfirmCheck').checked;
    
    if (!confirmCheck) {
        e.preventDefault();
        return false;
    }
    
    const message = `⚠️ KONFIRMASI RESTORE ⚠️\n\n` +
        `Anda akan melakukan restore dari backup:\n${backupFile}\n\n` +
        `Tindakan ini akan:\n` +
        `✗ MENIMPA seluruh database saat ini\n` +
        `✗ Menghapus semua perubahan yang belum dibackup\n` +
        `✗ Tindakan TIDAK DAPAT dibatalkan!\n\n` +
        `Apakah Anda YAKIN ingin melanjutkan?`;
    
    if (!confirm(message)) {
        e.preventDefault();
        return false;
    }
});

// Handle upload restore form submission
document.getElementById('uploadRestoreForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('backup_upload');
    const confirmCheck = document.getElementById('restoreConfirmCheck').checked;
    
    if (!confirmCheck) {
        e.preventDefault();
        return false;
    }
    
    if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        const fileSize = (fileInput.files[0].size / 1024 / 1024).toFixed(2);
        
        const message = `⚠️ KONFIRMASI RESTORE ⚠️\n\n` +
            `Anda akan melakukan restore dari file:\n${fileName}\nUkuran: ${fileSize} MB\n\n` +
            `Tindakan ini akan:\n` +
            `✗ MENIMPA seluruh database saat ini\n` +
            `✗ Menghapus semua perubahan yang belum dibackup\n` +
            `✗ Tindakan TIDAK DAPAT dibatalkan!\n\n` +
            `Apakah Anda YAKIN ingin melanjutkan?`;
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    }
});
</script>
