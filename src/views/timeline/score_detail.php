<?php
$stageLabel = $stageLabel ?? 'Tahap';
$stageCode = strtolower($stageCode ?? '');
$showFinalAverage = false;
$scoreData = $scoreData ?? null;
$downloadUrl = $downloadUrl ?? null;
$error = $error ?? null;
$student = $scoreData['student'] ?? null;
$evaluations = $scoreData['evaluations'] ?? [];
$defaultComponents = $scoreData['components'] ?? [];
$schedule = $scoreData['schedule'] ?? null;

$formatDateTime = static function (?string $value): string {
    return !empty($value) ? date('d M Y H:i', strtotime($value)) : '-';
};

$formatNumber = static function ($value, int $decimals = 2): string {
    if ($value === null || $value === '') {
        return '-';
    }
    return number_format((float)$value, $decimals);
};

$normalizeWeight = static function ($raw): array {
    $raw = $raw ?? 0;
    $rawFloat = (float)$raw;
    $percent = $rawFloat <= 1 ? $rawFloat * 100 : $rawFloat;
    $fraction = $rawFloat > 1 ? $rawFloat / 100 : $rawFloat;
    return [$percent, $fraction];
};
?>

<div class="row">
    <div class="col-12 mb-3">
        <h2>Nilai <?= htmlspecialchars($stageLabel) ?></h2>
        <p class="text-muted mb-0">Rekap nilai lengkap, sama dengan format PDF yang digunakan Kombi/dosen.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
    <a href="/timeline" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Timeline</a>
    <?php return; ?>
<?php endif; ?>

<?php if (!$scoreData || empty($evaluations)): ?>
    <div class="alert alert-warning">
        Nilai belum tersedia untuk tahap ini. Pastikan seluruh dosen telah mengisi penilaian.
    </div>
    <a href="/timeline" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Timeline</a>
    <?php return; ?>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <?php if ($student): ?>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Nama Mahasiswa</div>
                    <div class="fw-semibold"><?= htmlspecialchars($student['name'] ?? '-') ?></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">NIM</div>
                    <div class="fw-semibold"><?= htmlspecialchars($student['nim'] ?? '-') ?></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Angkatan</div>
                    <div class="fw-semibold"><?= htmlspecialchars($student['angkatan'] ?? '-') ?></div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Status Mahasiswa</div>
                    <div class="fw-semibold text-uppercase"><?= htmlspecialchars($student['status'] ?? '-') ?></div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($schedule): ?>
            <div class="row g-3 mt-2">
                <div class="col-sm-6">
                    <div class="text-muted small">Jadwal Terbaru</div>
                    <div class="fw-semibold"><?= htmlspecialchars($schedule['date_text'] ?? '-') ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small">Ruang</div>
                    <div class="fw-semibold"><?= htmlspecialchars($schedule['room'] ?? '-') ?></div>
                </div>
            </div>
        <?php endif; ?>
        <div class="mt-3 d-flex gap-2">
            <a href="/timeline" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Timeline
            </a>
            <?php if ($downloadUrl): ?>
                <a href="<?= htmlspecialchars($downloadUrl) ?>" class="btn btn-primary" target="_blank" rel="noopener">
                    <i class="fas fa-print"></i> Cetak PDF Nilai
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php foreach ($evaluations as $evaluation): ?>
    <?php
        $components = $evaluation['components'] ?? $defaultComponents;
        $finalScore = $evaluation['final_score'] ?? null;
    ?>
    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h5 class="mb-1"><?= htmlspecialchars($evaluation['evaluator_name'] ?? 'Dosen') ?></h5>
                <div class="text-muted small">
                    <?= htmlspecialchars($evaluation['role_label'] ?? ($evaluation['evaluator_role'] ?? '-')) ?>
                </div>
            </div>
            <div class="text-md-end mt-2 mt-md-0">
                <div class="fw-semibold fs-5"><?= $formatNumber($finalScore) ?></div>
                <div class="text-muted small">Nilai Akhir</div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3 g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Mode Penilaian</div>
                    <div class="fw-semibold text-uppercase"><?= htmlspecialchars($evaluation['mode'] ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Terakhir Diperbarui</div>
                    <div class="fw-semibold"><?= $formatDateTime($evaluation['updated_at'] ?? null) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">NIP/NIDN</div>
                    <div class="fw-semibold"><?= htmlspecialchars($evaluation['nip'] ?? '-') ?></div>
                </div>
            </div>
            <?php if (!empty($components)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Komponen Penilaian</th>
                                <th class="text-center">Bobot (%)</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Nilai x Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($components as $component): ?>
                                <?php
                                    [$weightPercent, $weightFraction] = $normalizeWeight($component['weight'] ?? 0);
                                    $score = isset($component['score']) ? (float)$component['score'] : null;
                                    $weightedScore = ($score !== null && $weightFraction > 0) ? $score * $weightFraction : null;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($component['name'] ?? '-') ?></td>
                                    <td class="text-center"><?= $formatNumber($weightPercent) ?></td>
                                    <td class="text-center"><?= $formatNumber($score) ?></td>
                                    <td class="text-center"><?= $formatNumber($weightedScore) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php
                            $totalWeighted = $evaluation['total_score'] ?? null;
                            $hasFooter = ($totalWeighted !== null) || ($showFinalAverage && $finalScore !== null);
                        ?>
                        <?php if ($hasFooter): ?>
                        <tfoot>
                            <?php if ($totalWeighted !== null): ?>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Jumlah (Σ Nilai x Bobot)</td>
                                <td class="text-center fw-semibold"><?= $formatNumber($totalWeighted) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($showFinalAverage && $finalScore !== null): ?>
                            <tr>
                                <td colspan="3" class="text-end fw-semibold">Nilai Akhir (Rata-rata)</td>
                                <td class="text-center fw-semibold"><?= $formatNumber($finalScore) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Nilai komponen tidak tersedia (mode nilai akhir langsung).</p>
            <?php endif; ?>
            <?php if (!empty($evaluation['notes'])): ?>
                <div class="alert alert-info mt-3 mb-0">
                    <div class="text-muted small">Catatan Dosen</div>
                    <div><?= nl2br(htmlspecialchars($evaluation['notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
