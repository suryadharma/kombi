<?php
$context = $context ?? [];
$components = $components ?? [];
$evaluation = $evaluation ?? null;
$evaluationComponents = $evaluationComponents ?? [];
$mode = $mode ?? 'components';
$existingNotes = $existingNotes ?? '';
$pembimbingSummary = $pembimbingSummary ?? [];

$componentScoresMap = [];
foreach ($evaluationComponents as $componentRow) {
    if (isset($componentRow['id'])) {
        $componentScoresMap[$componentRow['id']] = $componentRow['score'] ?? 0;
    }
}

$totalWeight = 0;
$totalWeightedScore = 0;
foreach ($components as $component) {
    $score = $componentScoresMap[$component['id']] ?? 0;
    $weighted = $score * $component['weight'];
    $totalWeight += $component['weight'];
    $totalWeightedScore += $weighted;
}
$normalizedScore = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) : 0;
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
            <div>
                <h2 class="mb-1">Panel Penguji Eksternal</h2>
                <p class="mb-0 text-muted">Form penilaian menggunakan format yang sama dengan penguji internal.</p>
            </div>
            <div class="card shadow-sm mt-3 mt-md-0" style="min-width: 260px;">
                <div class="card-body py-3">
                    <h6 class="text-uppercase text-muted mb-2">Identitas Penguji</h6>
                    <div class="d-flex align-items-start">
                        <div class="me-2">
                            <i class="fas fa-user-tie fa-lg text-success"></i>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($context['examiner_name'] ?? 'Penguji Eksternal') ?></div>
                            <div class="text-muted small mb-1"><?= htmlspecialchars($context['nip'] ?? '-') ?></div>
                            <span class="badge bg-secondary">Penguji Eksternal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Mahasiswa & Jadwal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Nama</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($context['student_name'] ?? '-') ?></dd>
                            
                            <dt class="col-sm-4">NIM</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($context['student_nim'] ?? '-') ?></dd>
                            
                            <dt class="col-sm-4">Judul</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($context['title'] ?? '-') ?></dd>
                        </dl>
                    </div>
                    
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Tahap</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $context['stage'] ?? '-'))) ?></dd>
                            
                            <dt class="col-sm-4">Tanggal</dt>
                            <dd class="col-sm-8"><?= isset($context['scheduled_at']) ? date('d M Y H:i', strtotime($context['scheduled_at'])) : '-' ?></dd>
                            
                            <dt class="col-sm-4">Ruangan</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($context['room'] ?? '-') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (($context['stage'] ?? '') === 'ujian'): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ringkasan Nilai Pra-Ujian</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Pembimbing 1</dt>
                        <dd class="col-sm-7"><?= isset($pembimbingSummary['pembimbing_1']['score']) ? number_format($pembimbingSummary['pembimbing_1']['score'], 2) : '-' ?></dd>
                        
                        <dt class="col-sm-5">Pembimbing 2</dt>
                        <dd class="col-sm-7"><?= isset($pembimbingSummary['pembimbing_2']['score']) ? number_format($pembimbingSummary['pembimbing_2']['score'], 2) : '-' ?></dd>
                        
                        <dt class="col-sm-5">Agregat</dt>
                        <dd class="col-sm-7"><strong><?= isset($pembimbingSummary['aggregate']) ? number_format($pembimbingSummary['aggregate'], 2) : '-' ?></strong></dd>
                    </dl>
                    <p class="text-muted small mb-0">Ditampilkan agar penguji skripsi mengetahui hasil pra-ujian dari pembimbing.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Form Penilaian</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/external/score/submit">
                    <?= Csrf::field(); ?>
                    <div id="components_section">
                        <?php if (empty($components)): ?>
                            <div class="alert alert-warning">
                                Komponen penilaian belum dikonfigurasi. Hubungi Kombi untuk mengatur komponen terlebih dahulu.
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Komponen Penilaian</th>
                                        <th class="text-center">Bobot</th>
                                        <th class="text-center">Nilai (0-100)</th>
                                        <th class="text-center">Nilai x Bobot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($components as $index => $component): 
                                        $score = $componentScoresMap[$component['id']] ?? null;
                                        $weightedScore = ($score !== null) ? ($score * $component['weight']) : 0;
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?= htmlspecialchars($component['name']) ?>
                                            <?php if (!empty($component['description'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($component['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format($component['weight'] * 100, 2) ?>%</td>
                                        <td>
                                            <input type="number"
                                                   class="form-control component-score"
                                                   name="score_<?= $component['id'] ?>"
                                                   min="0"
                                                   max="100"
                                                   step="0.01"
                                                   value="<?= $score !== null ? htmlspecialchars($score) : '' ?>"
                                                   data-weight="<?= $component['weight'] ?>"
                                                   required>
                                        </td>
                                        <td class="text-center weighted-score" id="weighted_score_<?= $component['id'] ?>">
                                            <?= number_format($weightedScore, 4) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Total Bobot:</strong></td>
                                        <td class="text-center"><strong><?= number_format($totalWeight * 100, 2) ?>%</strong></td>
                                        <td class="text-end"><strong>Total Nilai:</strong></td>
                                        <td class="text-center"><strong id="total_score_value"><?= number_format($totalWeightedScore, 4) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Nilai Akhir (Ternormalisasi):</strong></td>
                                        <?php $normalizedLetter = ScoreHelper::letterGrade($normalizedScore); ?>
                                        <td class="text-center" id="final_normalized_score">
                                            <strong id="final_normalized_value"><?= number_format($normalizedScore, 2) ?></strong>
                                            <span class="badge <?= $normalizedLetter ? 'bg-primary' : 'bg-secondary' ?> ms-2" id="final_normalized_letter">
                                                <?= $normalizedLetter ?: '—' ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Evaluasi</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4"><?= htmlspecialchars($existingNotes) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Simpan Penilaian
                        </button>
                        <a href="/external/logout" class="btn btn-secondary">Logout</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const componentInputs = document.querySelectorAll('.component-score');
    componentInputs.forEach((input) => {
        input.addEventListener('input', recalcScores);
    });

    function getLetterGrade(score) {
        if (typeof score !== 'number' || isNaN(score)) {
            return null;
        }
        if (score < 0) {
            return null;
        }
        if (score >= 80) return 'A';
        if (score >= 75) return 'AB';
        if (score >= 70) return 'B';
        if (score >= 65) return 'BC';
        if (score >= 60) return 'C';
        if (score >= 55) return 'CD';
        if (score >= 50) return 'D';
        if (score >= 45) return 'DE';
        if (score >= 0) return 'E';
        return null;
    }

    function recalcScores() {
        let totalWeight = 0;
        let totalWeightedScore = 0;

        componentInputs.forEach((input) => {
            const weight = parseFloat(input.dataset.weight) || 0;
            const score = parseFloat(input.value) || 0;
            const weightedScore = score * weight;

            totalWeight += weight;
            totalWeightedScore += weightedScore;

            const componentId = input.name.split('_')[1];
            const weightedCell = document.getElementById('weighted_score_' + componentId);
            if (weightedCell) {
                weightedCell.textContent = weightedScore.toFixed(4);
            }
        });

        const totalScoreValue = document.getElementById('total_score_value');
        const finalScoreValue = document.getElementById('final_normalized_value');
        const finalScoreLetter = document.getElementById('final_normalized_letter');
        if (totalScoreValue) {
            totalScoreValue.textContent = totalWeightedScore.toFixed(4);
        }
        if (finalScoreValue && finalScoreLetter) {
            const normalized = totalWeight > 0 ? (totalWeightedScore / totalWeight) : 0;
            finalScoreValue.textContent = normalized.toFixed(2);
            const letter = getLetterGrade(normalized);
            if (letter) {
                finalScoreLetter.textContent = letter;
                finalScoreLetter.classList.remove('bg-secondary');
                finalScoreLetter.classList.add('bg-primary');
            } else {
                finalScoreLetter.textContent = '—';
                finalScoreLetter.classList.remove('bg-primary');
                finalScoreLetter.classList.add('bg-secondary');
            }
        }
    }

    recalcScores();
});
</script>
