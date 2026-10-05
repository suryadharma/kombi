<?php
$finalScore = $finalScore ?? null;
$status = $status ?? 'invalid';
$message = $message ?? '';
?>

<div class="text-center mb-4">
    <span class="status-badge <?= $status === 'valid' ? 'status-valid' : 'status-invalid' ?>">
        <i class="fas <?= $status === 'valid' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
        <?= $status === 'valid' ? 'DOKUMEN VALID' : 'DOKUMEN TIDAK VALID' ?>
    </span>
</div>

<?php if ($status === 'valid' && $finalScore): ?>
    <div class="alert alert-success">
        <strong><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></strong>
    </div>

    <div class="info-card">
        <div class="label">Mahasiswa</div>
        <div class="value">
            <strong><?= htmlspecialchars($finalScore['student_name'] ?? '-') ?></strong><br>
            NIM: <?= htmlspecialchars($finalScore['nim'] ?? '-') ?><br>
            Angkatan: <?= htmlspecialchars($finalScore['angkatan'] ?? '-') ?>
        </div>
    </div>

    <?php if (!empty($finalScore['skripsi_title'])): ?>
    <div class="info-card">
        <div class="label">Judul Skripsi</div>
        <div class="value"><?= htmlspecialchars($finalScore['skripsi_title']) ?></div>
    </div>
    <?php endif; ?>

    <div class="info-card">
        <div class="label">Nilai Akhir Skripsi</div>
        <div class="value" style="font-size: 1.5rem; font-weight: bold; color: #667eea;">
            <?php
            $bypassData = json_decode($finalScore['bypass_data'] ?? '[]', true);
            if (is_array($bypassData) && !empty($bypassData[0])) {
                echo number_format((float) ($bypassData[0]['score'] ?? 0), 2);
            } else {
                echo '-';
            }
            ?>
        </div>
    </div>

    <div class="info-card">
        <div class="label">Nilai Huruf</div>
        <div class="value" style="font-size: 1.5rem; font-weight: bold; color: #667eea;">
            <?php
            $bypassData = json_decode($finalScore['bypass_data'] ?? '[]', true);
            if (is_array($bypassData) && !empty($bypassData[0])) {
                echo htmlspecialchars($bypassData[0]['letter_grade'] ?? '-');
            } else {
                echo '-';
            }
            ?>
        </div>
    </div>

    <?php
    $bypassData = json_decode($finalScore['bypass_data'] ?? '[]', true);
    if (is_array($bypassData) && !empty($bypassData[0]['is_bypass'])):
    ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Catatan:</strong> Nilai ini adalah hasil bypass evaluation oleh Kombi/Superadmin
    </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-danger">
        <strong><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($message) ?></strong>
    </div>
<?php endif; ?>
