<?php
$students = $students ?? [];
$availableStages = $availableStages ?? [];
$role = $role ?? '';
$filters = $filters ?? ['angkatan' => '', 'search' => ''];
$availableAngkatan = $availableAngkatan ?? [];
$usingActiveAngkatan = $usingActiveAngkatan ?? false;
?>

<div class="row">
    <div class="col-12">
        <h2>Penilaian Tahap Seminar & Sidang</h2>
        <p class="text-muted">Gunakan daftar berikut untuk mengisi atau meninjau nilai setiap mahasiswa sesuai peran
            Anda.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php
$filterConfig = [
    'id' => 'scoreFilterForm',
    'method' => 'GET',
    'action' => '/scores/submit',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Filter Angkatan',
            'value' => (string) ($filters['angkatan'] ?? ''),
            'options' => array_merge(
                [
                    ['value' => '', 'label' => 'Semua ' . ($usingActiveAngkatan ? 'Angkatan Aktif' : 'Angkatan')]
                ],
                array_map(function ($angkatan) {
                    return [
                        'value' => (string) $angkatan,
                        'label' => (string) $angkatan
                    ];
                }, $availableAngkatan)
            ),
            'auto_submit' => true,
            'col' => 3
        ],
        [
            'type' => 'search',
            'name' => 'search',
            'label' => 'Cari Mahasiswa',
            'placeholder' => 'NIM atau nama...',
            'value' => $filters['search'] ?? '',
            'col' => 4
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Cari', 'icon' => 'fas fa-search', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/scores/submit'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Mahasiswa</h5>
                <span class="badge bg-primary">Total: <?= count($students) ?> mahasiswa</span>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-info mb-0">
                        Tidak ada mahasiswa yang terasosiasi dengan akun Anda untuk saat ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="scoresTable">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <?php foreach ($availableStages as $stageCode => $stageLabel): ?>
                                        <th><?= htmlspecialchars($stageLabel) ?></th>
                                    <?php endforeach; ?>
                                    <th>Nilai Akhir Skripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['nim']) ?></td>
                                        <td>
                                            <div><?= htmlspecialchars($student['name']) ?></div>
                                            <?php
                                            if (!empty($student['assignment_role'])) {
                                                $roleMap = [
                                                    'pembimbing_1' => 'Pembimbing 1',
                                                    'pembimbing_2' => 'Pembimbing 2',
                                                    'penguji_1' => 'Ketua Penguji',
                                                    'penguji_2' => 'Penguji Anggota 1',
                                                    'penguji_3' => 'Penguji Anggota 2',
                                                    'sekretaris' => 'Sekretaris'
                                                ];

                                                $displayRoles = [];
                                                $rawRoles = explode(',', $student['assignment_role']);
                                                foreach ($rawRoles as $r) {
                                                    $r = trim($r);
                                                    if (isset($roleMap[$r])) {
                                                        $displayRoles[] = $roleMap[$r];
                                                    } else {
                                                        // Fallback for codes not in map
                                                        $displayRoles[] = ucwords(str_replace('_', ' ', $r));
                                                    }
                                                }

                                                if (!empty($displayRoles)) {
                                                    echo '<div class="small text-muted fst-italic">Sebagai: ' . htmlspecialchars(implode(', ', $displayRoles)) . '</div>';
                                                }
                                            }
                                            ?>
                                            <?php
                                            $letterLinks = $student['letter_links'] ?? [];
                                            $linkGroups = [
                                                'pembimbing' => 'Surat Tugas Pembimbing',
                                                'penguji' => 'Surat Tugas Penguji'
                                            ];
                                            $hasLetter = false;
                                            foreach ($linkGroups as $groupKey => $_label) {
                                                if (!empty($letterLinks[$groupKey])) {
                                                    $hasLetter = true;
                                                    break;
                                                }
                                            }
                                            if ($hasLetter): ?>
                                                <div class="mt-1">
                                                    <?php foreach ($linkGroups as $groupKey => $groupLabel):
                                                        $links = $letterLinks[$groupKey] ?? [];
                                                        if (empty($links)) {
                                                            continue;
                                                        }
                                                        ?>
                                                        <div class="small d-flex flex-wrap align-items-center gap-1">
                                                            <span class="text-muted"><?= htmlspecialchars($groupLabel) ?>:</span>
                                                            <?php foreach ($links as $link):
                                                                $statusClass = 'bg-light text-primary border';
                                                                $status = $link['status'] ?? null;
                                                                if ($status === 'replacement') {
                                                                    $statusClass = 'bg-warning text-dark border-warning';
                                                                } elseif ($status === 'former' || $status === 'history') {
                                                                    $statusClass = 'bg-secondary text-white border-0';
                                                                } elseif ($status === 'proposal') {
                                                                    $statusClass = 'bg-info text-dark border-info';
                                                                }
                                                                $statusLabel = $link['status_label'] ?? null;
                                                                $titleAttr = $statusLabel ? ('title="Status: ' . htmlspecialchars($statusLabel) . '"') : '';
                                                                ?>
                                                                <a href="<?= htmlspecialchars($link['url']) ?>" target="_blank"
                                                                    rel="noopener" class="badge <?= $statusClass ?>" <?= $titleAttr ?>>
                                                                    <i class="fas fa-external-link-alt"></i>
                                                                    <?= htmlspecialchars($link['label'] ?? 'Lihat Surat') ?>
                                                                </a>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <?php foreach ($availableStages as $stageCode => $stageLabel):
                                            $stageData = $student['stages'][$stageCode] ?? null;
                                            $status = $stageData['status'] ?? 'Belum Dinilai';
                                            $score = $stageData['score'] ?? null;
                                            $updatedAt = $stageData['updated_at'] ?? null;
                                            $isLocked = !empty($stageData['locked']);
                                            $lockReason = $stageData['lock_reason'] ?? '';
                                            $roleCanEditStage = !empty($stageData['can_edit']);
                                            $statusKey = strtolower($status);
                                            $badgeClass = 'bg-secondary';
                                            $statusLabel = $status;

                                            if ($isLocked) {
                                                $statusLabel = 'Terkunci';
                                                $badgeClass = 'bg-secondary';
                                            } elseif ($statusKey === 'components') {
                                                $badgeClass = 'bg-info';
                                            } elseif ($statusKey === 'final') {
                                                $badgeClass = 'bg-success';
                                            } elseif ($statusKey === 'bypass') {
                                                $badgeClass = 'bg-warning text-dark';
                                            }

                                            if (!empty($stageData['evaluations']) && $statusKey === 'belum dinilai') {
                                                $status = 'final';
                                                $statusKey = 'final';
                                                $statusLabel = 'Final';
                                                $badgeClass = 'bg-success';
                                            }
                                            ?>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge <?= $badgeClass ?>">
                                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', strtolower($statusLabel)))) ?>
                                                    </span>
                                                    <?php if (!empty($stageData['evaluations'])): ?>
                                                        <div class="d-flex flex-wrap gap-1">
                                                            <?php foreach ($stageData['evaluations'] as $evaluation):
                                                                $badgeTitleParts = [];
                                                                if (!empty($evaluation['name'])) {
                                                                    $badgeTitleParts[] = $evaluation['name'];
                                                                }
                                                                if (!empty($evaluation['mode'])) {
                                                                    $badgeTitleParts[] = ucwords(str_replace('-', ' ', $evaluation['mode']));
                                                                }
                                                                if (!empty($evaluation['updated_at'])) {
                                                                    $badgeTitleParts[] = date('d M Y H:i', strtotime($evaluation['updated_at']));
                                                                }
                                                                $badgeTitle = implode(' • ', $badgeTitleParts);
                                                                ?>
                                                                <?php
                                                                $displayScore = null;
                                                                if (isset($evaluation['weighted_total']) && $evaluation['weighted_total'] !== null) {
                                                                    $displayScore = (float) $evaluation['weighted_total'];
                                                                } elseif (isset($evaluation['final_score']) && $evaluation['final_score'] !== null) {
                                                                    $displayScore = (float) $evaluation['final_score'];
                                                                }
                                                                $scoreDisplay = $displayScore !== null ? number_format($displayScore, 2) : '-';
                                                                $statusClass = 'bg-light text-dark border small';
                                                                $status = $evaluation['assignment_status'] ?? null;
                                                                if ($status === 'replacement') {
                                                                    $statusClass = 'bg-warning text-dark border-warning small';
                                                                } elseif ($status === 'former' || $status === 'history') {
                                                                    $statusClass = 'bg-secondary text-white small';
                                                                }
                                                                $statusLabel = $evaluation['assignment_status_label'] ?? null;
                                                                ?>
                                                                <span class="badge <?= $statusClass ?>" <?= $badgeTitle !== '' ? 'title="' . htmlspecialchars($badgeTitle) . '"' : '' ?>>
                                                                    <?= htmlspecialchars($evaluation['label']) ?>: <?= $scoreDisplay ?>
                                                                    <?php if ($statusLabel && $status !== 'current'): ?>
                                                                        <span
                                                                            class="ms-1 text-uppercase"><?= htmlspecialchars($statusLabel) ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($evaluation['letter'])): ?>
                                                                        <span
                                                                            class="ms-1 text-primary fw-semibold">(<?= htmlspecialchars($evaluation['letter']) ?>)</span>
                                                                    <?php endif; ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php elseif (!$isLocked): ?>
                                                        <span class="text-muted small fst-italic">Belum ada nilai</span>
                                                    <?php endif; ?>
                                                    <?php if ($updatedAt): ?>
                                                        <span class="text-muted small">
                                                            <?= date('d M Y H:i', strtotime($updatedAt)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($isLocked && $lockReason): ?>
                                                        <span class="text-muted small"><?= htmlspecialchars($lockReason) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($stageCode === 'ujian' && !empty($stageData['exam_outcome'])): ?>
                                                        <?php
                                                        $outcomeUpper = strtoupper($stageData['exam_outcome']);
                                                        $outcomeBadge = 'bg-secondary';
                                                        if ($outcomeUpper === 'LULUS') {
                                                            $outcomeBadge = 'bg-success';
                                                        } elseif ($outcomeUpper === 'MENGULANG') {
                                                            $outcomeBadge = 'bg-danger';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $outcomeBadge ?>">
                                                            Status Ujian: <?= htmlspecialchars(ucfirst(strtolower($outcomeUpper))) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <?php
                                                        // Default tampilkan tombol kecuali dibatasi per role-stage
                                                        $showFormButton = true;
                                                        $studentAssignmentRole = $student['assignment_role'] ?? '';

                                                        if ($stageCode === 'pra-ujian') {
                                                            $isPembimbingForStudent = $studentAssignmentRole && strpos($studentAssignmentRole, 'pembimbing') !== false;
                                                            $showFormButton = $isPembimbingForStudent || in_array($role, ['kombi', 'superadmin'], true);
                                                        }

                                                        if ($stageCode === 'ujian') {
                                                            $isPengujiForStudent = $studentAssignmentRole && strpos($studentAssignmentRole, 'penguji') !== false;
                                                            $showFormButton = $isPengujiForStudent || in_array($role, ['kombi', 'superadmin'], true);
                                                        }

                                                        if ($showFormButton) {
                                                            $actionLabel = $roleCanEditStage ? 'Isi/Perbarui' : 'Lihat';
                                                            $actionIcon = $roleCanEditStage ? 'fas fa-pen' : 'fas fa-eye';
                                                            $actionBtnClass = $roleCanEditStage ? 'btn-primary' : 'btn-outline-secondary';
                                                            ?>
                                                            <a href="/evaluations/form/<?= $student['id'] ?>/<?= $stageCode ?>"
                                                                class="btn btn-sm <?= $actionBtnClass ?> <?= $isLocked ? 'disabled' : '' ?>"
                                                                <?= $isLocked ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                                                <i class="<?= $actionIcon ?>"></i> <?= $actionLabel ?>
                                                            </a>
                                                        <?php } ?>
                                                        <?php
                                                        $stageEvaluations = $stageData['evaluations'] ?? [];
                                                        if (!in_array($role, ['kombi', 'superadmin'], true)):
                                                            $hasStageEvaluation = !empty($stageEvaluations);
                                                            ?>
                                                            <?php if ($hasStageEvaluation): ?>
                                                                <a href="/scores/export/<?= $student['id'] ?>/<?= $stageCode ?>"
                                                                    class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener">
                                                                    <i class="fas fa-file-pdf"></i> Cetak
                                                                </a>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                                    title="Belum ada nilai untuk dicetak">
                                                                    <i class="fas fa-file-pdf"></i> Cetak
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php if (!empty($stageEvaluations)): ?>
                                                                <?php foreach ($stageEvaluations as $evaluation): ?>
                                                                    <?php if (!empty($evaluation['evaluator_id'])): ?>
                                                                        <a href="/scores/export/<?= $student['id'] ?>/<?= $stageCode ?>?evaluator=<?= (int) $evaluation['evaluator_id'] ?>"
                                                                            class="btn btn-sm btn-outline-danger" target="_blank" rel="noopener">
                                                                            <i class="fas fa-file-pdf"></i>
                                                                            <?= htmlspecialchars($evaluation['label'] ?? ($evaluation['evaluator_name'] ?? '')) ?>
                                                                        </a>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                                                    <i class="fas fa-file-pdf"></i> Cetak
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                        <td>
                                            <?php
                                            $finalScoreData = $student['final_thesis_score'] ?? null;
                                            $finalScoreVal = $finalScoreData['value'] ?? 0;
                                            $isBypass = ($finalScoreData['is_bypass'] ?? false) === true;
                                            $displayFinal = number_format($finalScoreVal, 2);
                                            
                                            // Get component values from formula parts
                                            $praUjianVal = 0;
                                            $ujianVal = 0;
                                            if (!empty($finalScoreData['formula']['parts'])) {
                                                foreach ($finalScoreData['formula']['parts'] as $part) {
                                                    if (($part['label'] ?? '') === 'Pra-Ujian') {
                                                        $praUjianVal = $part['value'] ?? 0;
                                                    } elseif (($part['label'] ?? '') === 'Ujian Skripsi') {
                                                        $ujianVal = $part['value'] ?? 0;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="d-flex flex-column gap-1">
                                                <?php if ($finalScoreVal > 0): ?>
                                                    <?php
                                                    $letter = $finalScoreData['letter'] ?? '';
                                                    $displayText = $displayFinal . ($letter ? " ({$letter})" : '');
                                                    ?>
                                                    <span
                                                        class="badge <?= $isBypass ? 'bg-warning text-dark' : 'bg-primary' ?> fs-6">
                                                        <?= $displayText ?>
                                                    </span>
                                                    <?php if ($isBypass): ?>
                                                        <span class="badge bg-light text-dark border small">BYPASS</span>
                                                    <?php else: ?>
                                                        <div class="small text-muted" title="Detail Perhitungan">
                                                            Pra-Ujian:
                                                            <?= number_format($praUjianVal, 2) ?><br>
                                                            Ujian: <?= number_format($ujianVal, 2) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <a href="/scores/export/<?= $student['id'] ?>/final"
                                                        class="btn btn-sm btn-outline-danger mt-1"
                                                        title="Cetak PDF Nilai Akhir Skripsi"
                                                        target="_blank">
                                                        <i class="fas fa-file-pdf"></i> Cetak
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Belum Lengkap</span>
                                                <?php endif; ?>

                                                <?php if ($role === 'kombi' || $role === 'superadmin'): ?>
                                                    <a href="/evaluations/bypass/<?= $student['id'] ?>/ujian"
                                                        class="btn btn-sm btn-outline-warning mt-1"
                                                        title="Input data mahasiswa lama / bypass nilai">
                                                        <i class="fas fa-forward"></i> Bypass / Input Manual
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableElement = $('#scoresTable');
        if (typeof $ !== 'undefined' && tableElement.length) {
            const scoresTable = tableElement.DataTable({
                dom: 'lrtip',
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                pageLength: 25,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                order: [[1, 'asc']]
            });

        }
    });
</script>