<?php
$students = $students ?? [];
$summary = $summary ?? [];
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
?>

<div class="row">
    <div class="col-12">
        <h2>Laporan Data Lulusan</h2>
        <p class="text-muted">Data lengkap mahasiswa yang telah lulus beserta informasi pembimbing, penguji, dan tahapan skripsi.</p>
    </div>
</div>

<?php
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$filterConfig = [
    'id' => 'graduatedFilterForm',
    'method' => 'GET',
    'action' => '/reports/graduated',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Angkatan',
            'value' => (string)($selectedAngkatan ?? ''),
            'options' => $angkatanOptions,
            'col' => 4
        ],
        [
            'type' => 'search',
            'name' => 'student_search',
            'label' => 'Cari (NIM/Nama)',
            'placeholder' => 'Masukkan NIM atau Nama',
            'value' => $currentSearch ?? '',
            'col' => 4
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/graduated'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<?php if (!empty($summary) && isset($summary['total_graduated'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan Lulusan</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded bg-success text-white">
                            <h4 class="mb-1"><?= htmlspecialchars($summary['total_graduated']) ?></h4>
                            <small>Total Lulusan</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1 text-primary">
                                <?= $summary['avg_study_duration'] !== null ? htmlspecialchars($summary['avg_study_duration']) . ' hari' : '-' ?>
                            </h4>
                            <small class="text-muted">Rata-rata Durasi (Sempro-Ujian)</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1 text-info"><?= count($summary['per_angkatan']) ?></h4>
                            <small class="text-muted">Angkatan Terdata</small>
                        </div>
                    </div>
                </div>
                <?php if (!empty($summary['per_angkatan'])): ?>
                <h6 class="mb-3">Ringkasan per Angkatan</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Angkatan</th>
                                <th>Jumlah Lulusan</th>
                                <th>Rata-rata Durasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary['per_angkatan'] as $angkatan => $data): ?>
                            <tr>
                                <td><?= htmlspecialchars($angkatan) ?></td>
                                <td><?= htmlspecialchars($data['count']) ?></td>
                                <td><?= $data['avg_duration'] !== null ? htmlspecialchars($data['avg_duration']) . ' hari' : '-' ?></td>
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
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Data Lulusan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="graduatedTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Angkatan</th>
                                <th>Sem. Masuk</th>
                                <th>Sem. Lulus</th>
                                <th>Masa Studi</th>
                                <th>Nilai Akhir</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Helper function to format evaluator label
function formatEvaluatorLabel(assignmentRole, evaluatorRole) {
    const assignmentLabels = {
        'pembimbing_1': 'Pembimbing 1',
        'pembimbing_2': 'Pembimbing 2',
        'penguji_1': 'Penguji Ketua',
        'penguji_2': 'Penguji Anggota 1',
        'penguji_3': 'Penguji Anggota 2',
    };
    if (assignmentRole && assignmentLabels[assignmentRole]) {
        return assignmentLabels[assignmentRole];
    }
    switch (evaluatorRole) {
        case 'dosen_pembimbing': return 'Pembimbing';
        case 'dosen_penguji': return 'Penguji';
        case 'kombi': return 'Kombi';
        case 'superadmin': return 'Superadmin';
        case 'penguji_eksternal': return 'Penguji Eksternal';
        default: return evaluatorRole || '-';
    }
}

// Helper function to display scores
function displayScores(stageLabel, scores, stageKey, studentId) {
    if (!scores || scores.length === 0) {
        return '<tr><td>' + stageLabel + '</td><td colspan="4" class="text-muted">Tidak ada data nilai</td></tr>';
    }
    
    let html = '';
    scores.forEach(function(score, index) {
        const roleLabel = formatEvaluatorLabel(score.assignment_role, score.evaluator_role);
        const displayValue = score.score !== null ? parseFloat(score.score).toFixed(2) : '-';
        const exportUrl = '/reports/graduated/' + studentId + '/export/' + stageKey + '?evaluator=' + score.evaluator_id;
        
        html += '<tr>';
        html += '<td>' + (index === 0 ? stageLabel : '') + '</td>';
        html += '<td>' + score.evaluator_name + ' (' + roleLabel + ')</td>';
        html += '<td><span class="badge bg-primary">' + displayValue + '</span></td>';
        html += '<td><a class="btn btn-sm btn-outline-danger" target="_blank" href="' + exportUrl + '"><i class="fas fa-file-pdf"></i> Cetak</a></td>';
        html += '<td><span class="text-muted">-</span></td>';
        html += '</tr>';
    });
    return html;
}

// Function to render detail row
function renderDetailRow(studentId, detailData) {
    const events = detailData.events || {};
    const assignments = detailData.assignments || {};
    const scores = detailData.scores || {};
    const finalScore = detailData.final_score || {};
    
    const title = detailData.title || '-';
    const semproDate = events.SEMPRO ? new Date(events.SEMPRO).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
    const semhasDate = events.SEMHAS ? new Date(events.SEMHAS).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
    const ujianDate = events.UJIAN_SKRIPSI ? new Date(events.UJIAN_SKRIPSI).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
    
    let html = '<div class="collapse" id="collapse-' + studentId + '">';
    html += '<div class="card bg-light">';
    html += '<div class="card-body">';
    html += '<h6 class="mb-3"><strong>Judul Skripsi:</strong> ' + title + '</h6>';
    
    // Scores table
    html += '<div class="mb-4">';
    html += '<table class="table table-sm table-bordered mb-0">';
    html += '<thead class="table-light"><tr><th>Tahap</th><th>Penguji</th><th>Nilai</th><th>Aksi</th><th>Detail</th></tr></thead>';
    html += '<tbody>';
    html += displayScores('Seminar Proposal', scores.sempro || [], 'sempro', studentId);
    html += displayScores('Seminar Hasil', scores.semhas || [], 'semhas', studentId);
    html += displayScores('Pra-Ujian', scores['pra-ujian'] || [], 'pra-ujian', studentId);
    html += displayScores('Ujian Skripsi', scores.ujian || [], 'ujian', studentId);
    
    // Final Score row
    if (finalScore.value !== null && finalScore.value !== undefined) {
        const finalScoreDisplay = parseFloat(finalScore.value).toFixed(2);
        const finalLetter = finalScore.letter || '-';
        const exportUrl = '/reports/graduated/' + studentId + '/export/final';
        
        html += '<tr class="table-success">';
        html += '<td><strong>Nilai Akhir Skripsi</strong></td>';
        html += '<td colspan="2">';
        html += '<span class="badge bg-primary fs-6">' + finalScoreDisplay + '</span> ';
        html += '<span class="badge bg-success fs-6">' + finalLetter + '</span>';
        html += '</td>';
        html += '<td><a class="btn btn-sm btn-outline-danger" target="_blank" href="' + exportUrl + '"><i class="fas fa-file-pdf"></i> Cetak</a></td>';
        html += '<td><small class="text-muted">Rata-rata Pra-Ujian (Pembimbing) + Rata-rata Ujian Skripsi (Penguji)</small></td>';
        html += '</tr>';
    }
    
    html += '</tbody></table></div>';
    
    // Events and assignments
    html += '<div class="row">';
    html += '<div class="col-md-6"><strong>Tanggal Sempro:</strong> ' + semproDate + '</div>';
    html += '<div class="col-md-6"><strong>Tanggal Semhas:</strong> ' + semhasDate + '</div>';
    html += '<div class="col-md-6"><strong>Tanggal Ujian:</strong> ' + ujianDate + '</div>';
    html += '<div class="col-md-6"><strong>Pembimbing 1:</strong> ' + (assignments.pembimbing_1 || '-') + '</div>';
    html += '<div class="col-md-6"><strong>Pembimbing 2:</strong> ' + (assignments.pembimbing_2 || '-') + '</div>';
    html += '<div class="col-md-6"><strong>Penguji 1:</strong> ' + (assignments.penguji_1 || '-') + '</div>';
    html += '<div class="col-md-6"><strong>Penguji 2:</strong> ' + (assignments.penguji_2 || '-') + '</div>';
    html += '<div class="col-md-6"><strong>Penguji 3:</strong> ' + (assignments.penguji_3 || '-') + '</div>';
    html += '</div>';
    
    html += '</div></div></div>';
    return html;
}

$(document).ready(function() {
    var table = $('#graduatedTable').DataTable({
        language: {
            url: "/public/vendor/datatables/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'asc']],
        ajax: {
            url: '/reports/graduated/data',
            data: function(d) {
                <?php if ($selectedAngkatan): ?>
                d.angkatan = <?= json_encode($selectedAngkatan) ?>;
                <?php endif; ?>
                <?php if ($currentSearch): ?>
                d.search = <?= json_encode($currentSearch) ?>;
                <?php endif; ?>
            },
            dataSrc: function(json) {
                // Store detail data for each row
                json.data.forEach(function(row) {
                    if (row[8]) {
                        try {
                            row.detailData = JSON.parse(row[8]);
                        } catch (e) {
                            row.detailData = {};
                        }
                    }
                });
                return json.data;
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    var finalScore = (row.detailData && row.detailData.final_score) || {};
                    if (finalScore.value !== null && finalScore.value !== undefined) {
                        return '<span class="badge bg-primary fs-6">' + parseFloat(finalScore.value).toFixed(2) + '</span> ' +
                               '<span class="badge bg-success fs-6">' + (finalScore.letter || '-') + '</span>';
                    }
                    return '<span class="badge bg-secondary fs-6">Belum tersedia</span>';
                }
            },
            { 
                data: 6,
                render: function(data, type, row) {
                    return data;
                }
            },
            // Hidden columns for detail data
            { data: 7, visible: false },
            { data: 8, visible: false }
        ],
        columnDefs: [
            { targets: 7, orderable: false }
        ],
        drawCallback: function(settings) {
            // Re-attach collapse handlers after each draw
            $('#graduatedTable tbody tr').not('.detail-row').each(function() {
                const $tr = $(this);
                const row = table.row($tr);
                const detailBtn = $tr.find('button[data-bs-target]');
                
                detailBtn.off('click').on('click', function(e) {
                    e.preventDefault();
                    const target = $(this).data('bs-target');
                    const studentId = target.replace('#detail-', '');
                    
                    // Check if detail row already exists
                    let $detailRow = $tr.next('.detail-row');
                    if ($detailRow.length === 0) {
                        // Create detail row
                        const detailHtml = renderDetailRow(studentId, row.data().detailData || {});
                        $detailRow = $('<tr class="detail-row"><td colspan="8" class="p-0">' + detailHtml + '</td></tr>');
                        $tr.after($detailRow);
                        // Show the collapse
                        const $collapse = $detailRow.find('.collapse');
                        $collapse.collapse('show');
                    } else {
                        // Detail row exists, check if it's visible
                        const $collapse = $detailRow.find('.collapse');
                        if ($collapse.hasClass('show')) {
                            // Currently visible, hide and remove
                            $collapse.collapse('hide');
                            setTimeout(function() {
                                $detailRow.remove();
                            }, 350); // Wait for collapse animation
                        } else {
                            // Currently hidden, show it
                            $collapse.collapse('show');
                        }
                    }
                });
            });
        }
    });
    
    // Update AJAX request when filter form is submitted
    $('#graduatedFilterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        table.settings()[0].ajax.data = function(d) {
            d.angkatan = params.angkatan || '';
            d.search = params.search || '';
        };
        table.ajax.reload();
    });
});
</script>
