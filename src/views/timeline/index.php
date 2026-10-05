<?php
$timeline = $timeline ?? [];
$scoreVisibility = $scoreVisibility ?? [];
$evaluationTimestamps = $evaluationTimestamps ?? [];
$finalScoreData = $finalScoreData ?? null;
$scoreStageLabels = [
    'sempro' => 'Seminar Proposal',
    'semhas' => 'Seminar Hasil',
    'pra-ujian' => 'Pra-Ujian Skripsi',
    'ujian' => 'Ujian Skripsi'
];
$studentId = $studentId ?? null;
?>

<div class="row">
    <div class="col-12">
        <h2>Timeline Perjalanan Skripsi</h2>
        <p class="text-muted mb-3">Pantau status setiap tahap dari pengajuan judul hingga ujian skripsi. Jika Kombi mengizinkan, tombol “Lihat Nilai” akan muncul di tahap yang sudah dinilai.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($timeline)): ?>
    <div class="alert alert-info">
        Timeline belum tersedia. Pastikan Anda telah terdaftar sebagai mahasiswa aktif di sistem.
    </div>
<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Status Tahap</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($timeline as $item): 
                        $status = strtolower($item['status']);
                        $badgeClass = 'bg-secondary';
                        if ($status === 'selesai') {
                            $badgeClass = 'bg-success';
                        } elseif ($status === 'proses') {
                            $badgeClass = 'bg-info';
                        } elseif ($status === 'terjadwal') {
                            $badgeClass = 'bg-primary';
                        }
                        $stageKey = $item['stage_code'] ?? null;
                        $isStageVisible = $stageKey && !empty($scoreVisibility[$stageKey]);
                        $hasScore = $stageKey && !empty($evaluationTimestamps[$stageKey]);
                        $canOpenScores = $isStageVisible && $hasScore && !empty($studentId);
                    ?>
                    <div class="timeline-item mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucwords($item['status'])) ?></span>
                            <h5 class="mb-0"><?= htmlspecialchars($item['label']) ?></h5>
                            <?php if ($stageKey): ?>
                                <a href="<?= $canOpenScores ? '/timeline/scores/' . urlencode($stageKey) : '#' ?>"
                                   class="btn btn-sm ms-auto <?= $canOpenScores ? 'btn-primary' : 'btn-outline-secondary disabled' ?>">
                                    Lihat Nilai
                                </a>
                            <?php endif; ?>
                        </div>
                        <p class="mb-1 mt-2 text-muted"><?= htmlspecialchars($item['description']) ?></p>
                        <?php if ($stageKey && !$canOpenScores): ?>
                            <small class="text-muted d-block mb-1">
                                Nilai belum dapat dibuka (<?= !$isStageVisible ? 'akses belum diaktifkan' : 'menunggu penilaian dosen' ?>).
                            </small>
                        <?php endif; ?>
                        <?php if (!empty($item['timestamp'])): ?>
                            <small class="text-muted">
                                Terakhir diperbarui: <?= date('d M Y H:i', strtotime($item['timestamp'])) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <hr class="my-2">
                    <?php endforeach; ?>
                    
                    <?php if ($finalScoreData !== null && !empty($scoreVisibility['final_score'])): ?>
                    <div class="timeline-item mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-success">Selesai</span>
                            <h5 class="mb-0">Nilai Akhir Skripsi</h5>
                        </div>
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-2">Nilai Akhir:</h6>
                                            <div>
                                                <span class="badge bg-primary fs-5"><?= number_format($finalScoreData['final_score'], 2) ?></span>
                                                <span class="badge bg-success fs-5 ms-2"><?= htmlspecialchars($finalScoreData['letter_grade']) ?></span>
                                                <?php if ($finalScoreData['has_bypass']): ?>
                                                <span class="badge bg-warning text-dark fs-6 ms-2"><i class="fas fa-forward"></i> Bypass</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="/reports/graduated/<?= htmlspecialchars($studentId) ?>/export/final" class="btn btn-sm btn-danger" target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i> PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mt-2 mb-0">
                                <small><i class="fas fa-info-circle me-1"></i>Selamat! Anda telah menyelesaikan semua tahap penilaian skripsi.</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
