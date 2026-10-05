<?php
$activeTab = $activeTab ?? 'bypass';
$rows = $rows ?? [];
$summary = $summary ?? ['total' => 0, 'stage_counts' => []];
$stageLabels = $stageLabels ?? [];
$stageCounts = $summary['stage_counts'] ?? [];
$stageFilter = $stageFilter ?? null;
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
?>

<div class="row">
    <div class="col-12">
        <h2>Laporan Bypass Nilai</h2>
        <p class="text-muted mb-0">Monitoring seluruh penggunaan fitur bypass nilai sebagai bahan evaluasi.</p>
    </div>
</div>

<?php include __DIR__ . '/_nav.php'; ?>

<?php
$stageOptions = [['value' => '', 'label' => 'Semua Tahap']];
foreach ($stageLabels as $stageKey => $stageName) {
    $stageOptions[] = ['value' => $stageKey, 'label' => $stageName];
}
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$filterConfig = [
    'id' => 'bypassFilterForm',
    'method' => 'GET',
    'action' => '/reports/bypass',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'stage',
            'label' => 'Tahap',
            'value' => (string)($stageFilter ?? ''),
            'options' => $stageOptions,
            'col' => 4
        ],
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
            'label' => 'Cari Mahasiswa',
            'placeholder' => 'NIM / Nama',
            'value' => $currentSearch ?? '',
            'col' => 4
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Terapkan', 'icon' => 'fas fa-filter', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/bypass'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Total Bypass</h6>
                <h3 class="mb-0"><?= htmlspecialchars($summary['total']) ?></h3>
                <small class="text-muted">Jumlah bypass yang tercatat</small>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Distribusi per Tahap</h6>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($stageCounts as $stage => $count): 
                        $label = $stageLabels[$stage] ?? ucwords(str_replace('-', ' ', $stage));
                        $percentage = ($summary['total'] ?? 0) > 0 ? round(($count / $summary['total']) * 100, 1) : 0;
                    ?>
                    <div>
                        <span class="fw-semibold"><?= htmlspecialchars($label) ?></span>
                        <div><?= htmlspecialchars($count) ?> bypass <span class="text-muted">(<?= $percentage ?>%)</span></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Rincian Bypass Nilai</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="bypassTable">
                        <thead class="table-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Tahap</th>
                                <th>Nilai</th>
                                <th>Catatan</th>
                                <th>Penginput</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#bypassTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        order: [[5, 'desc']],
        pageLength: 25,
        ajax: {
            url: '/reports/bypass/data',
            data: function(d) {
                <?php if ($stageFilter): ?>
                d.stage = <?= json_encode($stageFilter) ?>;
                <?php endif; ?>
                <?php if ($selectedAngkatan): ?>
                d.angkatan = <?= json_encode($selectedAngkatan) ?>;
                <?php endif; ?>
                <?php if ($currentSearch): ?>
                d.search = <?= json_encode($currentSearch) ?>;
                <?php endif; ?>
            }
        },
        columns: [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 }
        ]
    });
    
    // Update AJAX request when filter form is submitted
    $('#bypassFilterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        table.settings()[0].ajax.data = function(d) {
            d.stage = params.stage || '';
            d.angkatan = params.angkatan || '';
            d.search = params.search || '';
        };
        table.ajax.reload();
    });
});
</script>
