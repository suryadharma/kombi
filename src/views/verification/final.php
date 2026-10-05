<?php
$status = $status ?? 'invalid';
$message = $message ?? '';
$student = $evaluation ?? null;
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div
                class="card-header text-center <?= $status === 'valid' ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                <h4 class="mb-0">
                    <?= $status === 'valid' ? 'Dokumen Valid' : 'Verifikasi Gagal' ?>
                </h4>
            </div>
            <div class="card-body p-4">
                <p class="lead"><?= htmlspecialchars($message) ?></p>

                <?php if ($status === 'valid' && $student): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">Mahasiswa</h6>
                            <p class="mb-1 fw-semibold"><?= htmlspecialchars($student['name'] ?? '-') ?></p>
                            <p class="mb-3 text-muted">
                                NIM: <?= htmlspecialchars($student['nim'] ?? '-') ?><br>
                                Angkatan: <?= htmlspecialchars($student['angkatan'] ?? '-') ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted text-uppercase small">Tanggal Ujian</h6>
                            <p class="mb-1 fw-semibold">
                                <?= isset($student['ujian_date']) ? date('d F Y', strtotime($student['ujian_date'])) : '-' ?>
                            </p>
                            <?php if (isset($student['status'])): ?>
                                <span
                                    class="badge <?= $student['status'] === 'LULUS' ? 'bg-success' : 'bg-primary' ?> px-3 py-2">
                                    Status: <?= htmlspecialchars($student['status']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted text-uppercase small">Judul Skripsi</h6>
                            <p class="mb-1 fw-semibold"><?= htmlspecialchars($student['title'] ?? '-') ?></p>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase small">Dosen Pembimbing</h6>
                            <ul class="list-unstyled mb-0">
                                <?php if ($student['pembimbing_1']): ?>
                                    <li><i class="fas fa-user-tie me-1 text-muted"></i>
                                        <?= htmlspecialchars($student['pembimbing_1']) ?></li><?php endif; ?>
                                <?php if ($student['pembimbing_2']): ?>
                                    <li><i class="fas fa-user-tie me-1 text-muted"></i>
                                        <?= htmlspecialchars($student['pembimbing_2']) ?></li><?php endif; ?>
                                <?php if (!$student['pembimbing_1'] && !$student['pembimbing_2']): ?>
                                    <li>-</li><?php endif; ?>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase small">Dosen Penguji</h6>
                            <ul class="list-unstyled mb-0">
                                <?php if ($student['penguji_1']): ?>
                                    <li><i class="fas fa-user-check me-1 text-muted"></i>
                                        <?= htmlspecialchars($student['penguji_1']) ?></li><?php endif; ?>
                                <?php if ($student['penguji_2']): ?>
                                    <li><i class="fas fa-user-check me-1 text-muted"></i>
                                        <?= htmlspecialchars($student['penguji_2']) ?></li><?php endif; ?>
                                <?php if ($student['penguji_3']): ?>
                                    <li><i class="fas fa-user-check me-1 text-muted"></i>
                                        <?= htmlspecialchars($student['penguji_3']) ?></li><?php endif; ?>
                                <?php if (!$student['penguji_1'] && !$student['penguji_2'] && !$student['penguji_3']): ?>
                                    <li>-</li><?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-info mt-2">
                        <small><i class="fas fa-info-circle"></i> Dokumen ini diverifikasi menggunakan QR Code yang terdapat
                            pada PDF Transkrip Nilai Skripsi.</small>
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