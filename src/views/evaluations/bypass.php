<?php
$student = isset($student) ? $student : [];
$stage = isset($stage) ? $stage : '';
$evaluation = isset($evaluation) ? $evaluation : null;

// Stage labels
$stageLabels = [
    'sempro' => 'Seminar Proposal',
    'semhas' => 'Seminar Hasil',
    'pra-ujian' => 'Pra-Ujian Skripsi',
    'ujian' => 'Ujian Skripsi'
];
?>

<div class="row">
    <div class="col-12">
        <h2>Bypass Nilai - <?= htmlspecialchars($stageLabels[$stage] ?? $stage) ?></h2>
        <p>Form bypass nilai untuk mahasiswa (digunakan dalam kasus khusus)</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">NIM</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($student['nim'] ?? '') ?></dd>

                    <dt class="col-sm-4">Nama</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($student['name'] ?? '') ?></dd>

                    <dt class="col-sm-4">Judul Skripsi</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($student['thesis_title'] ?? '-') ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Penilaian</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Tahap</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($stageLabels[$stage] ?? $stage) ?></dd>

                    <dt class="col-sm-4">Mode</dt>
                    <dd class="col-sm-8"><span class="badge bg-warning">Bypass</span></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Form Bypass Nilai</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php
                $existingScore = $evaluation ? ScoreHelper::normalize($evaluation['final_score']) : null;
                $existingLetter = ScoreHelper::letterGrade($existingScore);
                $selectedOutcome = $currentExamOutcome ?? '';
                ?>
                <form method="POST" action="/evaluations/bypass/<?= $student['id'] ?? '' ?>/<?= $stage ?>/save">
                    <?= Csrf::field(); ?>

                    <div class="mb-4 border-bottom pb-3">
                        <h6 class="text-muted mb-3"><i class="fas fa-book"></i> Data Skripsi</h6>
                        <div class="mb-3">
                            <label for="thesis_title" class="form-label">Judul Skripsi</label>
                            <input type="text" class="form-control" id="thesis_title" name="thesis_title"
                                value="<?= htmlspecialchars($currentTitle ?? '') ?>"
                                placeholder="Masukkan judul skripsi...">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small text-uppercase fw-bold text-muted">Dosen
                                    Pembimbing</label>
                                <?php foreach (['pembimbing_1' => 'Pembimbing 1', 'pembimbing_2' => 'Pembimbing 2'] as $roleKey => $label):
                                    $currentVal = $assignments[$roleKey] ?? '';
                                    ?>
                                    <div class="mb-2">
                                        <label for="<?= $roleKey ?>" class="form-label small mb-1"><?= $label ?></label>
                                        <select class="form-select form-select-sm" name="<?= $roleKey ?>"
                                            id="<?= $roleKey ?>">
                                            <option value="">-- Pilih Dosen --</option>
                                            <?php foreach ($lecturers as $lecturer): ?>
                                                <option value="<?= $lecturer['id'] ?>"
                                                    <?= (int) $currentVal === (int) $lecturer['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($lecturer['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-uppercase fw-bold text-muted">Dosen Penguji</label>
                                <?php foreach (['penguji_1' => 'Ketua Penguji', 'penguji_2' => 'Ahggota Penguji 1', 'penguji_3' => 'Anggota Penguji 2'] as $roleKey => $label):
                                    $currentVal = $assignments[$roleKey] ?? '';
                                    ?>
                                    <div class="mb-2">
                                        <label for="<?= $roleKey ?>" class="form-label small mb-1"><?= $label ?></label>
                                        <select class="form-select form-select-sm" name="<?= $roleKey ?>"
                                            id="<?= $roleKey ?>">
                                            <option value="">-- Pilih Dosen --</option>
                                            <?php foreach ($lecturers as $lecturer): ?>
                                                <option value="<?= $lecturer['id'] ?>"
                                                    <?= (int) $currentVal === (int) $lecturer['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($lecturer['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="final_score" class="form-label">Nilai Bypass / Nilai Akhir</label>
                        <input type="number" class="form-control" id="final_score" name="final_score" min="0" max="100"
                            step="0.01"
                            value="<?= $existingScore !== null ? htmlspecialchars(number_format($existingScore, 2, '.', '')) : '' ?>"
                            required>
                        <div class="form-text">
                            Nilai huruf: <span class="badge bg-primary"
                                id="final_score_letter"><?= $existingLetter ?: '—' ?></span>
                        </div>
                    </div>

                    <?php if (strtolower($stage) === 'ujian'): ?>
                        <div class="mb-3">
                            <label for="exam_outcome" class="form-label">Status Sidang Skripsi <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="exam_outcome" name="exam_outcome" required>
                                <option value="">Pilih status...</option>
                                <option value="LULUS" <?= $selectedOutcome === 'LULUS' ? 'selected' : '' ?>>Lulus</option>
                                <option value="MENGULANG" <?= $selectedOutcome === 'MENGULANG' ? 'selected' : '' ?>>Mengulang
                                    Ujian</option>
                            </select>
                            <div class="form-text">Status ini akan diperbarui ke data mahasiswa.</div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Bypass <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            required><?= $evaluation ? htmlspecialchars($evaluation['notes']) : '' ?></textarea>
                        <div class="form-text">Tuliskan catatan singkat sebagai dasar bypass.</div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-warning">Simpan Bypass Nilai</button>
                        <a href="/students" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Petunjuk</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Gunakan fitur bypass hanya dalam kasus khusus</li>
                    <li>Catatan bypass harus ditulis dengan jelas</li>
                    <li>Nilai bypass akan tercatat sebagai <code>sumber=bypass</code></li>
                    <li>Semua bypass tercatat dalam audit trail</li>
                    <li>Status sidang akan otomatis disesuaikan dengan pilihan Anda di atas</li>
                </ul>

                <div class="alert alert-warning">
                    <strong>Perhatian!</strong> Fitur bypass hanya untuk digunakan oleh Kombi dan Superadmin dalam
                    situasi khusus.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scoreInput = document.getElementById('final_score');
        const letterBadge = document.getElementById('final_score_letter');

        function getLetter(score) {
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

        function refreshLetter() {
            if (!scoreInput || !letterBadge) {
                return;
            }
            const value = parseFloat(scoreInput.value);
            const letter = getLetter(value);
            if (letter) {
                letterBadge.textContent = letter;
                letterBadge.classList.add('bg-primary');
                letterBadge.classList.remove('bg-secondary');
            } else {
                letterBadge.textContent = '—';
                letterBadge.classList.add('bg-secondary');
                letterBadge.classList.remove('bg-primary');
            }
        }

        if (scoreInput) {
            scoreInput.addEventListener('input', refreshLetter);
            refreshLetter();
        }
    });
</script>