<?php
$maxPembimbing = $maxPembimbing ?? 8;
$maxPenguji = $maxPenguji ?? 10;
$totals = $totals ?? ['dosen' => 0, 'total_bimbingan' => 0, 'total_penguji' => 0];
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
?>

<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Laporan Beban Dosen</h2>
            <p class="text-muted mb-0">Pantau pemerataan mahasiswa yang dibimbing maupun diuji oleh setiap dosen.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_nav.php'; ?>

<?php
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$filterConfig = [
    'id' => 'workloadFilterForm',
    'method' => 'GET',
    'action' => '/reports/workload',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Filter Angkatan Mahasiswa',
            'value' => (string)($selectedAngkatan ?? ''),
            'options' => $angkatanOptions,
            'col' => 4
        ],
        [
            'type' => 'search',
            'name' => 'lecturer_search',
            'label' => 'Cari Dosen',
            'placeholder' => 'Nama dosen',
            'value' => $currentSearch ?? '',
            'col' => 4
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Terapkan', 'icon' => 'fas fa-filter', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/workload'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Jumlah Dosen</h6>
                <h3 class="mb-0"><?= htmlspecialchars($totals['dosen']) ?></h3>
                <small class="text-muted">Total dosen aktif dalam penugasan</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Total Bimbingan</h6>
                <h3 class="mb-0"><?= htmlspecialchars($totals['total_bimbingan']) ?></h3>
                <small class="text-muted">Akumulasi mahasiswa yang sedang dibimbing</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Total Pengujian</h6>
                <h3 class="mb-0"><?= htmlspecialchars($totals['total_penguji']) ?></h3>
                <small class="text-muted">Akumulasi mahasiswa yang diuji oleh dosen</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Distribusi Beban Per Dosen</h5>
                <span class="badge bg-light text-dark">Kuota lunak: &le; <?= $maxPembimbing ?> bimbingan, &le; <?= $maxPenguji ?> pengujian</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="workloadTable">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Dosen</th>
                                <th class="text-center">Mahasiswa Dibimbing</th>
                                <th class="text-center">Mahasiswa Diuji</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="small text-muted mt-2">
                        <span class="badge bg-primary me-2"> &nbsp; </span> Bimbingan dalam batas wajar
                        <span class="badge bg-success ms-3 me-2"> &nbsp; </span> Pengujian dalam batas wajar
                        <span class="badge bg-danger ms-3 me-2"> &nbsp; </span> Beban melebihi kuota
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var maxPembimbing = <?= $maxPembimbing ?>;
    var maxPenguji = <?= $maxPenguji ?>;
    
    // Build initial URL parameters from PHP values
    var initialParams = [];
    <?php if ($selectedAngkatan): ?>
    initialParams.push('angkatan=<?= urlencode($selectedAngkatan) ?>');
    <?php endif; ?>
    <?php if ($currentSearch): ?>
    initialParams.push('search=<?= urlencode($currentSearch) ?>');
    <?php endif; ?>
    var initialUrl = '/reports/workload/data' + (initialParams.length > 0 ? '?' + initialParams.join('&') : '');
    
    var table = $('#workloadTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'asc']],
        searching: false, // Disable DataTables built-in search
        dom: '<"top">rt<"bottom"lip>', // Remove default search box
        ajax: initialUrl,
        columns: [
            { 
                data: 1,
                render: function(data, type, row) {
                    return '<i class="fas fa-user-tie text-secondary me-2"></i><strong>' + data + '</strong>';
                }
            },
            { 
                data: 2,
                render: function(data, type, row) {
                    var count = parseInt(data) || 0;
                    var badgeClass = count > maxPembimbing ? 'bg-danger' : 'bg-primary';
                    var html = '<span class="badge ' + badgeClass + ' px-3 py-2">' + count + ' mahasiswa</span>';
                    if (count > maxPembimbing) {
                        html += '<div class="text-danger small mt-1">Melebihi kuota ' + maxPembimbing + '</div>';
                    }
                    return html;
                }
            },
            { 
                data: 3,
                render: function(data, type, row) {
                    var count = parseInt(data) || 0;
                    var badgeClass = count > maxPenguji ? 'bg-danger' : 'bg-success';
                    var html = '<span class="badge ' + badgeClass + ' px-3 py-2">' + count + ' mahasiswa</span>';
                    if (count > maxPenguji) {
                        html += '<div class="text-danger small mt-1">Melebihi kuota ' + maxPenguji + '</div>';
                    }
                    return html;
                }
            }
        ]
    });
    
    // Store reference to table for use in submit handler
    var workloadTable = table;
    
    // Override native submit method to intercept direct calls
    var form = $('#workloadFilterForm')[0];
    var originalSubmit = form.submit;
    form.submit = function() {
        // Trigger our AJAX handler instead of native submit
        $('#workloadFilterForm').trigger('submit-custom');
        return false; // Prevent native submit
    };
    
    // Custom submit handler that does the AJAX call
    $('#workloadFilterForm').on('submit-custom', function() {
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        
        // Build query parameters (only include non-empty values)
        var queryParams = Object.keys(params)
            .filter(function(key) {
                return params[key] !== '';
            })
            .map(function(key) {
                return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            })
            .join('&');
        
        // Build new URL
        var newUrl = '/reports/workload/data';
        if (queryParams) {
            newUrl += '?' + queryParams;
        }
        
        // Update ajax URL and reload
        workloadTable.ajax.url(newUrl).load();
    });
    
    // Also bind to regular submit event (for button clicks)
    $('#workloadFilterForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Trigger our custom handler
        $(this).trigger('submit-custom');
        return false;
    });
});
</script>
