<?php
$student = isset($student) ? $student : [];
$stage = isset($stage) ? $stage : '';
$components = isset($components) ? $components : [];
$evaluation = isset($evaluation) ? $evaluation : null;
$evaluationComponents = isset($evaluationComponents) ? $evaluationComponents : [];
$mode = $evaluation ? $evaluation['mode'] : 'components';
$examOutcomeEditable = isset($examOutcomeEditable) ? (bool) $examOutcomeEditable : false;
$currentExamOutcome = isset($currentExamOutcome) ? $currentExamOutcome : null;
$userAssignmentRole = isset($userAssignmentRole) ? $userAssignmentRole : null;
$availableEvaluations = isset($availableEvaluations) ? $availableEvaluations : [];
$selectedEvaluatorId = isset($selectedEvaluatorId) ? $selectedEvaluatorId : ($_SESSION['user_id'] ?? null);
$canSwitchEvaluator = isset($canSwitchEvaluator) ? (bool) $canSwitchEvaluator : false;
$roleCanEdit = isset($roleCanEdit) ? (bool) $roleCanEdit : false;
$activeEvaluator = isset($activeEvaluator) ? $activeEvaluator : [
    'id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['user_name'] ?? '',
    'role' => $_SESSION['role'] ?? '',
    'assignment_role' => null
];
$editingDisabled = !$roleCanEdit || ($canSwitchEvaluator && $selectedEvaluatorId === null);

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
        <h2>Form Penilaian - <?= htmlspecialchars($stageLabels[$stage] ?? $stage) ?></h2>
        <p>Menilai mahasiswa berdasarkan komponen yang telah dikonfigurasi</p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (!$roleCanEdit): ?>
            <div class="alert alert-info mb-0">
                Anda hanya dapat melihat penilaian pada tahap ini.
            </div>
        <?php endif; ?>
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

                    <?php if ($stage === 'ujian'): ?>
                        <dt class="col-sm-4">Status Sidang</dt>
                        <dd class="col-sm-8">
                            <?php if ($currentExamOutcome): ?>
                                <?php
                                $outcomeUpper = strtoupper($currentExamOutcome);
                                $outcomeBadge = 'badge bg-secondary';
                                if ($outcomeUpper === 'LULUS') {
                                    $outcomeBadge = 'badge bg-success';
                                } elseif ($outcomeUpper === 'MENGULANG') {
                                    $outcomeBadge = 'badge bg-danger';
                                }
                                ?>
                                <span
                                    class="<?= $outcomeBadge ?>"><?= htmlspecialchars(ucfirst(strtolower($outcomeUpper))) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Belum ditetapkan</span>
                            <?php endif; ?>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Penilai</h5>
            </div>
            <div class="card-body">
                <?php if ($canSwitchEvaluator && !empty($availableEvaluations)): ?>
                    <form method="GET" class="mb-3">
                        <label for="evaluatorSelector" class="form-label">Tampilkan penilaian milik</label>
                        <select class="form-select" id="evaluatorSelector" name="evaluator_id"
                            onchange="this.form.submit()">
                            <?php foreach ($availableEvaluations as $option):
                                $optionId = (int) $option['evaluator_id'];
                                $label = $option['evaluator_name'] ?? ('Penilai #' . $optionId);
                                $updatedAt = $option['updated_at'] ?? null;
                                if ($updatedAt) {
                                    $label .= ' · ' . date('d M Y H:i', strtotime($updatedAt));
                                }
                                ?>
                                <option value="<?= htmlspecialchars((string) $optionId) ?>"
                                    <?= ($optionId === (int) $selectedEvaluatorId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php elseif ($canSwitchEvaluator && empty($availableEvaluations)): ?>
                    <div class="alert alert-info small mb-3">
                        Belum ada penilaian yang tercatat. Nilai akan muncul setelah pembimbing/penguji mengisi formnya.
                    </div>
                <?php endif; ?>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($activeEvaluator['name'] ?? '-') ?></dd>

                    <dt class="col-sm-4">Peran</dt>
                    <dd class="col-sm-8">
                        <?php
                        // Show assignment role if available, otherwise show base role
                        if (isset($activeEvaluator['assignment_role']) && $activeEvaluator['assignment_role']) {
                            $assignmentLabels = [
                                'penguji_1' => 'Penguji Ketua',
                                'penguji_2' => 'Penguji Anggota 1',
                                'penguji_3' => 'Penguji Anggota 2',
                                'pembimbing_1' => 'Pembimbing 1',
                                'pembimbing_2' => 'Pembimbing 2'
                            ];
                            echo htmlspecialchars($assignmentLabels[$activeEvaluator['assignment_role']] ?? ucfirst(str_replace('_', ' ', $activeEvaluator['assignment_role'])));
                        } else {
                            $roleLabel = $activeEvaluator['role'] ?? '';
                            switch ($roleLabel) {
                                case 'dosen':
                                    echo 'Dosen';
                                    break;
                                case 'dosen_pembimbing':
                                    echo 'Pembimbing';
                                    break;
                                case 'dosen_penguji':
                                    echo 'Penguji';
                                    break;
                                case 'penguji_eksternal':
                                    echo 'Penguji Eksternal';
                                    break;
                                default:
                                    echo ucfirst(str_replace('_', ' ', $roleLabel ?: 'Administrator'));
                            }
                        }
                        ?>
                    </dd>

                    <?php if ($userAssignmentRole && !$canSwitchEvaluator): ?>
                        <dt class="col-sm-4">Penetapan</dt>
                        <dd class="col-sm-8">
                            <?php
                            $assignmentLabels = [
                                'penguji_1' => 'Penguji Ketua',
                                'penguji_2' => 'Penguji Anggota 1',
                                'penguji_3' => 'Penguji Anggota 2',
                                'pembimbing_1' => 'Pembimbing 1',
                                'pembimbing_2' => 'Pembimbing 2'
                            ];
                            echo htmlspecialchars($assignmentLabels[$userAssignmentRole] ?? ucfirst(str_replace('_', ' ', $userAssignmentRole)));
                            ?>
                        </dd>
                    <?php endif; ?>

                    <dt class="col-sm-4">Tahap</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($stageLabels[$stage] ?? $stage) ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Form Penilaian</h5>
            </div>
            <div class="card-body">
                <?php
                $showEvaluatorPrompt = $roleCanEdit && $canSwitchEvaluator && $selectedEvaluatorId === null;
                ?>
                <?php if ($showEvaluatorPrompt): ?>
                    <div class="alert alert-info">
                        Pilih penilai pada daftar di atas untuk melihat atau mengedit nilai yang sudah diinput.
                    </div>
                <?php endif; ?>
                <form method="POST" action="/evaluations/form/<?= $student['id'] ?? '' ?>/<?= $stage ?>/save">
                    <?= Csrf::field(); ?>
                    <?php if ($selectedEvaluatorId !== null): ?>
                        <input type="hidden" name="evaluator_id"
                            value="<?= htmlspecialchars((string) $selectedEvaluatorId) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Mode Penilaian</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_components"
                                    value="components" <?= $mode == 'components' ? 'checked' : '' ?> <?= ($editingDisabled ? 'disabled' : '') ?> <?= ($_SESSION['role'] == 'kombi' || $_SESSION['role'] == 'superadmin') ? '' : 'disabled' ?>>
                                <label class="form-check-label" for="mode_components">Komponen Berbobot</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="mode" id="mode_final" value="final"
                                    <?= $mode == 'final' ? 'checked' : '' ?> <?= ($editingDisabled ? 'disabled' : '') ?>
                                    <?= ($_SESSION['role'] == 'kombi' || $_SESSION['role'] == 'superadmin') ? '' : 'disabled' ?>>
                                <label class="form-check-label" for="mode_final">Nilai Akhir Langsung</label>
                            </div>
                        </div>
                    </div>

                    <!-- Components Mode -->
                    <div id="components_section" style="<?= $mode == 'final' ? 'display: none;' : '' ?>">
                        <?php if (empty($components)): ?>
                            <div class="alert alert-warning">
                                <p>Tidak ada komponen penilaian yang dikonfigurasi untuk tahap ini.</p>
                                <?php if ($_SESSION['role'] == 'kombi' || $_SESSION['role'] == 'superadmin'): ?>
                                    <p>Silakan <a href="/components">konfigurasi komponen penilaian</a> terlebih dahulu.</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="50%">Komponen Penilaian</th>
                                            <th width="15%">Bobot</th>
                                            <th width="15%">Nilai (0-100)</th>
                                            <th width="15%">Nilai Bobot</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalWeight = 0;
                                        $totalWeightedScore = 0;
                                        
                                        foreach ($components as $index => $component):
                                            $score = 0;
                                            // Check if we have existing score for this component
                                            foreach ($evaluationComponents as $ec) {
                                                if ($ec['id'] == $component['id'] && !is_null($ec['score'])) {
                                                    $score = $ec['score'];
                                                    break;
                                                }
                                            }

                                            // For pra-ujian/ujian stage, check if this is an auto-filled component
                                            $isReadonly = isset($component['readonly']) && $component['readonly'];
                                            if ($isReadonly) {
                                                if (isset($component['auto_score'])) {
                                                    $score = $component['auto_score'];
                                                } elseif (isset($component['description']) && preg_match('/([\d.]+)$/', $component['description'], $matches)) {
                                                    $score = $matches[1];
                                                }
                                            }

                                            $weightedScore = $score * $component['weight'];
                                            $totalWeight += $component['weight'];
                                            $totalWeightedScore += $weightedScore;
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <?= htmlspecialchars($component['name']) ?>
                                                    <?php if (!empty($component['description'])): ?>
                                                        <br><small
                                                            class="text-muted"><?= htmlspecialchars($component['description']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?= (floatval($component['weight']) * 100) ?>%</td>
                                                <td>
                                                    <?php if ($isReadonly): ?>
                                                        <input type="number" class="form-control component-score"
                                                            name="score_<?= $component['id'] ?>" min="0" max="100" step="0.01"
                                                            value="<?= $score ?>" data-weight="<?= $component['weight'] ?>"
                                                            readonly>
                                                        <input type="hidden" name="score_<?= $component['id'] ?>"
                                                            value="<?= $score ?>">
                                                    <?php else: ?>
                                                        <input type="number" class="form-control component-score"
                                                            name="score_<?= $component['id'] ?>" min="0" max="100" step="0.01"
                                                            value="<?= $score ?>" <?= $editingDisabled ? 'readonly' : '' ?>
                                                            data-weight="<?= $component['weight'] ?>" required>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center weighted-score"
                                                    id="weighted_score_<?= $component['id'] ?>">
                                                    <?= number_format($weightedScore, 4) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td colspan="2" class="text-end"><strong>Total Bobot:</strong></td>
                                            <td class="text-center"><strong><?= ($totalWeight * 100) ?>%</strong></td>
                                            <td class="text-end"><strong>Total Nilai:</strong></td>
                                            <td class="text-center"><strong
                                                    id="total_score_value"><?= number_format($totalWeightedScore, 4) ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Nilai Akhir (Ternormalisasi):</strong>
                                            </td>
                                            <?php
                                            $normalizedScore = $totalWeight > 0 ? ($totalWeightedScore / $totalWeight) : 0;
                                            $normalizedLetter = ScoreHelper::letterGrade($normalizedScore);
                                            ?>
                                            <td class="text-center" id="final_normalized_score">
                                                <strong
                                                    id="final_normalized_value"><?= number_format($normalizedScore, 2) ?></strong>
                                                <?php if ($normalizedLetter): ?>
                                                    <span class="badge bg-primary ms-2"
                                                        id="final_normalized_letter"><?= htmlspecialchars($normalizedLetter) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2 d-none"
                                                        id="final_normalized_letter">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Final Mode -->
                    <div id="final_section" style="<?= $mode == 'components' ? 'display: none;' : '' ?>">
                        <div class="mb-3">
                            <label for="final_score" class="form-label">Nilai Akhir</label>
                            <?php
                            $existingFinalScore = $evaluation ? ScoreHelper::normalize($evaluation['final_score']) : null;
                            $existingFinalLetter = ScoreHelper::letterGrade($existingFinalScore);
                            ?>
                            <input type="number" class="form-control" id="final_score" name="final_score" min="0"
                                max="100" step="0.01"
                                value="<?= $existingFinalScore !== null ? htmlspecialchars(number_format($existingFinalScore, 2, '.', '')) : '' ?>"
                                <?= $editingDisabled ? 'readonly' : '' ?> <?= ($mode == 'final') ? 'required' : '' ?>>
                            <div class="form-text">
                                Nilai huruf:
                                <span class="badge bg-primary"
                                    id="final_score_letter_display"><?= $existingFinalLetter ?: '—' ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($stage === 'ujian' && ($roleCanEdit || $examOutcomeEditable)): ?>
                        <div class="mb-3">
                            <label for="exam_outcome" class="form-label">Status Sidang Skripsi</label>
                            <?php if ($examOutcomeEditable): ?>
                                <select class="form-select" id="exam_outcome" name="exam_outcome" required>
                                    <option value="">Pilih status...</option>
                                    <option value="LULUS" <?= ($currentExamOutcome === 'LULUS') ? 'selected' : '' ?>>Lulus</option>
                                    <option value="MENGULANG" <?= ($currentExamOutcome === 'MENGULANG') ? 'selected' : '' ?>>
                                        Mengulang Ujian</option>
                                </select>
                                <div class="form-text">Pilihan ini akan memperbarui status mahasiswa pada daftar mahasiswa.
                                </div>
                            <?php else: ?>
                                <select class="form-select" disabled>
                                    <option value="">Pilih status...</option>
                                    <option value="LULUS" <?= ($currentExamOutcome === 'LULUS') ? 'selected' : '' ?>>Lulus</option>
                                    <option value="MENGULANG" <?= ($currentExamOutcome === 'MENGULANG') ? 'selected' : '' ?>>
                                        Mengulang Ujian</option>
                                </select>
                                <div class="form-text text-danger">
                                    <i class="fas fa-lock"></i> Hanya Ketua Penguji yang dapat mengubah status kelulusan.
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" <?= $editingDisabled ? 'readonly' : '' ?>><?= $evaluation ? htmlspecialchars($evaluation['notes']) : '' ?></textarea>
                    </div>

                    <?php if ($evaluation && !$editingDisabled): ?>
                        <div class="mb-3 alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Edit Nilai:</strong> Anda sedang mengedit nilai yang sudah ada. Perubahan akan dicatat dalam audit log.
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_reason" class="form-label fw-bold text-danger">Alasan Perubahan Nilai <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_reason" name="edit_reason" rows="2" required placeholder="Jelaskan mengapa nilai diubah..."></textarea>
                            <div class="form-text">Wajib diisi saat mengedit nilai yang sudah ada.</div>
                        </div>
                    <?php endif; ?>

                    <?php if ($evaluation && in_array($role ?? '', ['kombi', 'superadmin'])): ?>
                        <div class="mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <i class="fas fa-lock me-2"></i>Kontrol Kunci Nilai
                                    </h6>
                                    <p class="card-text small text-muted mb-2">
                                        Kombi/Superadmin dapat mengunci atau membuka nilai ini secara manual.
                                    </p>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleLockBtn">
                                        <i class="fas fa-lock me-1"></i> <span id="lockBtnText">Mengunci...</span>
                                    </button>
                                    <span id="lockStatus" class="ms-2"></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" <?= $editingDisabled ? 'disabled' : '' ?>>Simpan
                            Penilaian</button>
                        <a href="/scores/submit" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        // Mode switching
        const modeComponents = document.getElementById('mode_components');
        const modeFinal = document.getElementById('mode_final');
        const componentsSection = document.getElementById('components_section');
        const finalSection = document.getElementById('final_section');

        if (modeComponents && modeFinal) {
            modeComponents.addEventListener('change', function () {
                if (this.checked) {
                    componentsSection.style.display = 'block';
                    finalSection.style.display = 'none';
                }
            });

            modeFinal.addEventListener('change', function () {
                if (this.checked) {
                    componentsSection.style.display = 'none';
                    finalSection.style.display = 'block';
                }
            });
        }

        // Real-time calculation for component scores
        const componentScores = document.querySelectorAll('.component-score');
        componentScores.forEach(input => {
            input.addEventListener('input', calculateScores);
        });

        function calculateScores() {
            let totalWeight = 0;
            let totalWeightedScore = 0;

            componentScores.forEach(input => {
                // Skip readonly fields
                if (input.hasAttribute('readonly')) {
                    const weight = parseFloat(input.dataset.weight) || 0;
                    const score = parseFloat(input.value) || 0;
                    const weightedScore = score * weight;

                    totalWeight += weight;
                    totalWeightedScore += weightedScore;

                    // Update weighted score display
                    const componentId = input.name.split('_')[1];
                    const weightedScoreElement = document.getElementById('weighted_score_' + componentId);
                    if (weightedScoreElement) {
                        weightedScoreElement.textContent = weightedScore.toFixed(4);
                    }
                    return;
                }

                const weight = parseFloat(input.dataset.weight) || 0;
                const score = parseFloat(input.value) || 0;
                const weightedScore = score * weight;

                totalWeight += weight;
                totalWeightedScore += weightedScore;

                // Update weighted score display
                const componentId = input.name.split('_')[1];
                const weightedScoreElement = document.getElementById('weighted_score_' + componentId);
                if (weightedScoreElement) {
                    weightedScoreElement.textContent = weightedScore.toFixed(4);
                }
            });

            // Update total score display
            const totalScoreElement = document.getElementById('total_score_value');
            if (totalScoreElement) {
                totalScoreElement.textContent = totalWeightedScore.toFixed(4);
            }

            // Update normalized final score
            const normalizedScoreElement = document.getElementById('final_normalized_score');
            const normalizedValueElement = document.getElementById('final_normalized_value');
            const normalizedLetterBadge = document.getElementById('final_normalized_letter');
            if (normalizedScoreElement && normalizedValueElement && normalizedLetterBadge) {
                const normalizedScore = totalWeight > 0 ? (totalWeightedScore / totalWeight) : 0;
                normalizedValueElement.textContent = normalizedScore.toFixed(2);
                const letter = getLetterGrade(normalizedScore);
                if (letter) {
                    normalizedLetterBadge.textContent = letter;
                    normalizedLetterBadge.classList.remove('bg-secondary', 'd-none');
                    normalizedLetterBadge.classList.add('bg-primary');
                } else {
                    normalizedLetterBadge.textContent = '—';
                    normalizedLetterBadge.classList.remove('bg-primary', 'd-none');
                    normalizedLetterBadge.classList.add('bg-secondary');
                }
            }
        }

        // Initial calculation
        calculateScores();

        // Real-time letter for final score mode
        const finalScoreInput = document.getElementById('final_score');
        const finalLetterBadge = document.getElementById('final_score_letter_display');
        function updateFinalLetter() {
            if (!finalScoreInput || !finalLetterBadge) {
                return;
            }
            const value = parseFloat(finalScoreInput.value);
            const letter = getLetterGrade(value);
            if (letter) {
                finalLetterBadge.textContent = letter;
                finalLetterBadge.classList.remove('bg-secondary');
                finalLetterBadge.classList.add('bg-primary');
            } else {
                finalLetterBadge.textContent = '—';
                finalLetterBadge.classList.remove('bg-primary');
                finalLetterBadge.classList.add('bg-secondary');
            }
        }
        if (finalScoreInput && finalLetterBadge) {
            finalScoreInput.addEventListener('input', updateFinalLetter);
            updateFinalLetter();
        }

        // Lock button functionality for kombi/superadmin
        const toggleLockBtn = document.getElementById('toggleLockBtn');
        if (toggleLockBtn) {
            // Get evaluation ID from the form action or data attribute
            let evaluationId = null;
            
            <?php if ($evaluation): ?>
                evaluationId = <?= (int)($evaluation['id'] ?? 0) ?>;
                
                // Initial lock status
                const isLocked = <?= ($evaluation['locked_at'] ?? null) ? 'true' : 'false' ?>;
                updateLockButton(isLocked);
            <?php endif; ?>

            toggleLockBtn.addEventListener('click', function() {
                if (!evaluationId) {
                    alert('ID Evaluasi tidak ditemukan');
                    return;
                }

                const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
                if (!csrfToken) {
                    alert('Token keamanan tidak ditemukan');
                    return;
                }

                // Disable button during request
                toggleLockBtn.disabled = true;
                toggleLockBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';

                const formData = new FormData();
                formData.append('csrf_token', csrfToken);

                fetch(`/evaluations/${evaluationId}/toggle-lock`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    const text = await response.text();
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            updateLockButton(data.is_locked == 1);
                            // Show success message
                            const lockStatus = document.getElementById('lockStatus');
                            if (lockStatus) {
                                lockStatus.innerHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i>' + data.message + '</span>';
                                setTimeout(() => {
                                    lockStatus.innerHTML = '';
                                }, 3000);
                            }
                        } else {
                            alert(data.message || 'Gagal mengubah status kunci');
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        alert('Terjadi kesalahan saat memproses permintaan');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Gagal mengubah status kunci: ' + error.message);
                })
                .finally(() => {
                    toggleLockBtn.disabled = false;
                });
            });

            function updateLockButton(isLocked) {
                const lockBtnText = document.getElementById('lockBtnText');
                const lockIcon = toggleLockBtn.querySelector('i');
                
                if (isLocked) {
                    lockBtnText.textContent = 'Buka Kunci';
                    lockIcon.className = 'fas fa-unlock me-1';
                    toggleLockBtn.classList.remove('btn-outline-primary');
                    toggleLockBtn.classList.add('btn-outline-warning');
                } else {
                    lockBtnText.textContent = 'Kunci Nilai';
                    lockIcon.className = 'fas fa-lock me-1';
                    toggleLockBtn.classList.remove('btn-outline-warning');
                    toggleLockBtn.classList.add('btn-outline-primary');
                }
            }
        }
    });
</script>
