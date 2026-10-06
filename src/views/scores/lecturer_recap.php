<?php
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
$role = $role ?? '';
?>

<div class="row">
    <div class="col-12">
        <h2>Rekap Nilai Mahasiswa</h2>
        <p class="text-muted">Daftar mahasiswa yang telah Anda nilai, beserta nilai akhir skripsi.</p>
    </div>
</div>

<?php
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$filterConfig = [
    'id' => 'lecturerRecapFilterForm',
    'method' => 'GET',
    'action' => '/scores/lecturer-recap',
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
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/scores/lecturer-recap'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="lecturerRecapTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Angkatan</th>
                                <th>Status</th>
                                <th>Sem. Lulus</th>
                                <th>Nilai Akhir</th>
                                <th>Judul Skripsi</th>
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
        return '<tr><td>' + stageLabel + '</td><td colspan="2" class="text-muted">Tidak ada data nilai</td></tr>';
    }
    
    let html = '';
    scores.forEach(function(score, index) {
        const roleLabel = formatEvaluatorLabel(score.assignment_role, score.evaluator_role);
        const displayValue = score.score !== null && score.score !== undefined ? parseFloat(score.score).toFixed(2) : '-';
        const bypassBadge = score.is_bypass ? ' <span class="badge bg-warning text-dark"><i class="fas fa-forward"></i> Bypass</span>' : '';
        
        html += '<tr>';
        html += '<td>' + (index === 0 ? stageLabel : '') + '</td>';
        html += '<td>' + roleLabel + '</td>';
        html += '<td><span class="badge bg-primary">' + displayValue + '</span>' + bypassBadge + '</td>';
        html += '</tr>';
    });
    return html;
}

// Function to render detail row
function renderDetailRow(studentId, detailData) {
    const scores = detailData || {};
    
    let html = '<div class="collapse" id="collapse-' + studentId + '">';
    html += '<div class="card bg-light">';
    html += '<div class="card-body">';
    
    // Scores table
    html += '<div class="mb-4">';
    html += '<table class="table table-sm table-bordered mb-0">';
    html += '<thead class="table-light"><tr><th>Tahap</th><th>Peran</th><th>Nilai</th></tr></thead>';
    html += '<tbody>';
    html += displayScores('Seminar Proposal', scores.sempro || [], 'sempro', studentId);
    html += displayScores('Seminar Hasil', scores.semhas || [], 'semhas', studentId);
    html += displayScores('Pra-Ujian', scores['pra-ujian'] || [], 'pra-ujian', studentId);
    html += displayScores('Ujian Skripsi', scores.ujian || [], 'ujian', studentId);
    html += '</tbody></table></div>';
    
    html += '<div class="alert alert-info mb-0">';
    html += '<small><i class="fas fa-info-circle"></i> Halaman ini menampilkan nilai yang Anda berikan sebagai dosen pembimbing/penguji.</small>';
    html += '</div>';
    
    html += '</div></div></div>';
    return html;
}

$(document).ready(function() {
    var table = $('#lecturerRecapTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        searching: false,  // Disable built-in search, we use custom filter form
        pageLength: 25,
        order: [[0, 'asc']],
        ajax: {
            url: '/scores/lecturer-recap/data',
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
            { data: 6 },
            {
                data: 7,
                render: function(data, type, row) {
                    return data;
                }
            },
            // Hidden columns for detail data
            { data: 8, visible: false }
        ],
        columnDefs: [
            { targets: 7, orderable: false }
        ],
        drawCallback: function(settings) {
            // Re-attach collapse handlers after each draw
            $('#lecturerRecapTable tbody tr').not('.detail-row').each(function() {
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
    $('#lecturerRecapFilterForm').on('submit', function(e) {
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
});
</script>
