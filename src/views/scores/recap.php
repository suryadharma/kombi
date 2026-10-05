<?php
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
$role = $role ?? '';
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-clipboard-list"></i> Rekap Nilai Mahasiswa Lulus</h2>
        <p class="text-muted">Rekapitulasi nilai dari semua tahap dan nilai akhir skripsi untuk mahasiswa yang sudah lulus.</p>
    </div>
</div>

<?php
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$filterConfig = [
    'id' => 'recapFilterForm',
    'method' => 'GET',
    'action' => '/scores/recap',
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
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/scores/recap'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Formula Nilai Akhir:</strong> 
            Nilai Akhir = Rata-rata Nilai Pra-Ujian (Pembimbing) + Rata-rata Nilai Ujian Skripsi (Penguji)
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Mahasiswa Lulus</h5>
                <button class="btn btn-sm btn-outline-primary" id="expandAllBtn">
                    <i class="fas fa-expand-alt"></i> Expand All
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="recapTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Angkatan</th>
                                <th>Sem. Lulus</th>
                                <th>Nilai Akhir</th>
                                <th>Judul Skripsi</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.score-detail-table {
    font-size: 0.9rem;
}
.score-detail-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.stage-section {
    margin-bottom: 1rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    background-color: #f8f9fa;
}
.stage-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}
.final-score-display {
    font-size: 1.25rem;
    font-weight: bold;
}
</style>

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

// Function to render detail row
function renderDetailRow(studentId, scoresDetail) {
    const scores = scoresDetail || {};
    
    let html = '<div class="collapse" id="collapse-' + studentId + '">';
    html += '<div class="card bg-light">';
    html += '<div class="card-body">';
    
    // Scores by stage
    const stages = [
        { key: 'sempro', label: 'Seminar Proposal' },
        { key: 'semhas', label: 'Seminar Hasil' },
        { key: 'pra-ujian', label: 'Pra-Ujian' },
        { key: 'ujian', label: 'Ujian Skripsi' }
    ];
    
    stages.forEach(function(stage) {
        const stageScores = scores[stage.key] || [];
        if (stageScores.length > 0) {
            html += '<div class="stage-section">';
            html += '<div class="stage-title">' + stage.label + '</div>';
            html += '<table class="table table-sm table-bordered score-detail-table mb-0">';
            html += '<thead><tr><th>Penguji</th><th>Peran</th><th>Nilai</th></tr></thead>';
            html += '<tbody>';
            stageScores.forEach(function(score) {
                const roleLabel = formatEvaluatorLabel(score.assignment_role, '');
                html += '<tr>';
                html += '<td>' + score.evaluator_name + '</td>';
                html += '<td>' + roleLabel + '</td>';
                html += '<td><span class="badge bg-primary">' + (score.score ? parseFloat(score.score).toFixed(2) : '-') + '</span></td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            html += '</div>';
        }
    });
    
    html += '</div></div></div>';
    return html;
}

$(document).ready(function() {
    var table = $('#recapTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'asc']],
        ajax: {
            url: '/scores/recap/data',
            data: function(d) {
                <?php if ($selectedAngkatan): ?>
                d.angkatan = <?= json_encode($selectedAngkatan) ?>;
                <?php endif; ?>
                <?php if ($currentSearch): ?>
                d.student_search = <?= json_encode($currentSearch) ?>;
                <?php endif; ?>
            },
            dataSrc: function(json) {
                // Store detail data for each row
                json.data.forEach(function(row) {
                    // Column6 is detail button, column7 is scoresDetail JSON
                    if (row[7]) {
                        try {
                            row.scoresDetail = JSON.parse(row[7]);
                        } catch (e) {
                            row.scoresDetail = {};
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
                data: 6,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-outline-primary detail-btn"><i class="fas fa-chevron-down"></i> Detail</button>';
                }
            },
            // Hidden column for detail data (scoresDetail)
            { data: 7, visible: false }
        ],
        columnDefs: [
            { targets: 6, orderable: false }
        ],
        createdRow: function(row, data, dataIndex) {
            // Store title on the row for later use
            const nim = data[0];
            const title = data[5];
            $(row).attr('data-student-id', nim);
            $(row).data('title', title);
        },
        drawCallback: function(settings) {
            // Re-attach collapse handlers after each draw
            $('#recapTable tbody tr').not('.detail-row').each(function() {
                const $tr = $(this);
                const row = table.row($tr);
                const detailBtn = $tr.find('.detail-btn');
                
                detailBtn.off('click').on('click', function(e) {
                    e.preventDefault();
                    const nim = $tr.attr('data-student-id');
                    
                    // Check if detail row already exists
                    let $detailRow = $tr.next('.detail-row');
                    if ($detailRow.length === 0) {
                        // Create detail row
                        const detailHtml = renderDetailRow(nim, row.data().scoresDetail || {});
                        $detailRow = $('<tr class="detail-row"><td colspan="8" class="p-0">' + detailHtml + '</td></tr>');
                        $tr.after($detailRow);
                        // Show the collapse
                        const $collapse = $detailRow.find('.collapse');
                        $collapse.collapse('show');
                        detailBtn.html('<i class="fas fa-chevron-up"></i> Tutup');
                    } else {
                        // Detail row exists, check if it's visible
                        const $collapse = $detailRow.find('.collapse');
                        if ($collapse.hasClass('show')) {
                            // Currently visible, hide and remove
                            $collapse.collapse('hide');
                            setTimeout(function() {
                                $detailRow.remove();
                            }, 350);
                            detailBtn.html('<i class="fas fa-chevron-down"></i> Detail');
                        } else {
                            // Currently hidden, show it
                            $collapse.collapse('show');
                            detailBtn.html('<i class="fas fa-chevron-up"></i> Tutup');
                        }
                    }
                });
            });
        }
    });
    
    // Update AJAX request when filter form is submitted
    $('#recapFilterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        table.settings()[0].ajax.data = function(d) {
            d.angkatan = params.angkatan || '';
            d.student_search = params.student_search || '';
        };
        table.ajax.reload();
    });

    // Expand All button
    $('#expandAllBtn').on('click', function() {
        const $btn = $(this);
        const isExpanded = $btn.data('expanded') || false;
        
        if (isExpanded) {
            // Collapse all
            $('.detail-row').each(function() {
                const $collapse = $(this).find('.collapse');
                $collapse.collapse('hide');
                setTimeout(function() {
                    $(this).remove();
                }.bind(this), 350);
            });
            $btn.html('<i class="fas fa-expand-alt"></i> Expand All');
            $btn.data('expanded', false);
        } else {
            // Expand all visible rows
            $('#recapTable tbody tr').not('.detail-row').each(function() {
                const $tr = $(this);
                const $detailRow = $tr.next('.detail-row');
                
                if ($detailRow.length === 0) {
                    const row = table.row($tr);
                    const nim = $tr.attr('data-student-id');
                    const detailBtn = $tr.find('.detail-btn');
                    
                    const detailHtml = renderDetailRow(nim, row.data().scoresDetail || {});
                    const $newDetailRow = $('<tr class="detail-row"><td colspan="7" class="p-0">' + detailHtml + '</td></tr>');
                    $tr.after($newDetailRow);
                    
                    const $collapse = $newDetailRow.find('.collapse');
                    $collapse.collapse('show');
                    detailBtn.html('<i class="fas fa-chevron-up"></i> Tutup');
                }
            });
            $btn.html('<i class="fas fa-compress-alt"></i> Collapse All');
            $btn.data('expanded', true);
        }
    });
});
</script>
