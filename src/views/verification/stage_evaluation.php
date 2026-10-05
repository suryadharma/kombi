<?php
$evaluation = $evaluation ?? null;
$status = $status ?? 'invalid';
$message = $message ?? '';
?>

<div class="text-center mb-4">
    <span class="status-badge <?= $status === 'valid' ? 'status-valid' : 'status-invalid' ?>">
        <i class="fas <?= $status === 'valid' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
        <?= $status === 'valid' ? 'DOKUMEN VALID' : 'DOKUMEN TIDAK VALID' ?>
    </span>
</div>

<?php if ($status === 'valid' && $evaluation): ?>
    <div class="alert alert-success">
        <strong><i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?></strong>
    </div>

    <div class="info-card">
        <div class="label">Mahasiswa</div>
        <div class="value">
            <strong><?= htmlspecialchars($evaluation['student_name'] ?? '-') ?></strong><br>
            NIM: <?= htmlspecialchars($evaluation['nim'] ?? '-') ?><br>
            Angkatan: <?= htmlspecialchars($evaluation['angkatan'] ?? '-') ?>
        </div>
    </div>

    <div class="info-card">
        <div class="label">Penilai</div>
        <div class="value">
            <strong><?= htmlspecialchars($evaluation['evaluator_name'] ?? '-') ?></strong><br>
            NIP/NIDN: <?= htmlspecialchars($evaluation['nip'] ?? '-') ?>
        </div>
    </div>

    <div class="info-card">
        <div class="label">Role</div>
        <div class="value"><?= htmlspecialchars($evaluation['role_label'] ?? '-') ?></div>
    </div>

    <div class="info-card">
        <div class="label">Tahap</div>
        <div class="value">
            <?php
            $stageLabels = [
                'sempro' => 'Seminar Proposal',
                'semhas' => 'Seminar Hasil',
                'pra-ujian' => 'Pra-Ujian Skripsi',
                'ujian' => 'Sidang Skripsi',
            ];
            $stage = strtolower($evaluation['stage'] ?? '');
            echo htmlspecialchars($stageLabels[$stage] ?? ($evaluation['stage'] ?? '-'));
            ?>
        </div>
    </div>

    <div class="info-card">
        <div class="label">Σ Nilai x Bobot</div>
        <div class="value" style="font-size: 1.5rem; font-weight: bold; color: #667eea;">
            <?= $evaluation['weighted_total'] !== null ? number_format((float) $evaluation['weighted_total'], 2) : '-' ?>
        </div>
    </div>

    <?php if (!empty($evaluation['updated_at'])): ?>
    <div class="info-card">
        <div class="label">Informasi Tambahan</div>
        <div class="value">
            Terakhir diperbarui: <?= date('d M Y H:i', strtotime($evaluation['updated_at'])) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($evaluation['mode']) && $evaluation['mode'] === 'bypass'): ?>
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
