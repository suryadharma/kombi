<?php
$status = $status ?? 'invalid';
$message = $message ?? '';
$evaluation = $evaluation ?? null;
$title = $title ?? 'Verifikasi Nilai';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header text-center <?= $status === 'valid' ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                <h4 class="mb-0">
                    <?= $status === 'valid' ? 'Dokumen Valid' : 'Verifikasi Gagal' ?>
                </h4>
            </div>
            <div class="card-body p-4">
                <p class="lead"><?= htmlspecialchars($message) ?></p>

                <?php if ($status === 'valid' && $evaluation): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">Mahasiswa</h6>
                            <p class="mb-1 fw-semibold"><?= htmlspecialchars($evaluation['student_name'] ?? '-') ?></p>
                            <p class="mb-3 text-muted">
                                NIM: <?= htmlspecialchars($evaluation['nim'] ?? '-') ?><br>
                                Angkatan: <?= htmlspecialchars($evaluation['angkatan'] ?? '-') ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">Penilai</h6>
                            <p class="mb-1 fw-semibold"><?= htmlspecialchars($evaluation['evaluator_name'] ?? '-') ?></p>
                            <p class="mb-3 text-muted">
                                Role: <?= htmlspecialchars($evaluation['role_label'] ?? '-') ?><br>
                                NIP/NIDN: <?= htmlspecialchars($evaluation['nip'] ?? '-') ?>
                            </p>
                        </div>
                    </div>

                    <?php
                        $displayScore = $evaluation['weighted_total'] ?? $evaluation['total_score'] ?? $evaluation['final_score'] ?? null;
                        $normalizedScore = $evaluation['final_score'] ?? null;
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">Tahap</h6>
                            <p class="fw-semibold"><?= htmlspecialchars(strtoupper($evaluation['stage'] ?? '-')) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">Σ Nilai x Bobot</h6>
                            <p class="fw-bold fs-4 text-primary">
                                <?= $displayScore !== null ? htmlspecialchars(number_format((float)$displayScore, 2)) : '-' ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-muted text-uppercase small">Informasi Tambahan</h6>
                        <ul class="list-unstyled">
                            <li>
                                <span class="text-muted">Metode Input:</span>
                                <?= htmlspecialchars(strtoupper($evaluation['mode'] ?? '-')) ?>
                            </li>
                            <li>
                                <span class="text-muted">Terakhir diperbarui:</span>
                                <?= isset($evaluation['updated_at']) ? date('d M Y H:i', strtotime($evaluation['updated_at'])) : '-' ?>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="/" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
