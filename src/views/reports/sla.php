<?php
$activeTab = $activeTab ?? 'sla';
$rows = $rows ?? [];
$summary = $summary ?? [];
$thresholdHours = $thresholdHours ?? 72;
$stageLabels = $stageLabels ?? [
    'sempro' => 'Seminar Proposal',
    'semhas' => 'Seminar Hasil',
    'pra-ujian' => 'Pra-Ujian',
    'ujian' => 'Ujian Skripsi'
];
$angkatanList = $angkatanList ?? [];
$selectedStage = $selectedStage ?? null;
$selectedAngkatan = $selectedAngkatan ?? null;
$statusFilter = $statusFilter ?? null;
$currentSearch = $currentSearch ?? '';

$coverage = ($summary['total_events'] ?? 0) > 0
    ? round(($summary['with_entry'] ?? 0) / $summary['total_events'] * 100, 1)
    : 0;
$onTimePercent = ($summary['with_entry'] ?? 0) > 0
    ? round(($summary['on_time'] ?? 0) / $summary['with_entry'] * 100, 1)
    : 0;
$averageHours = $summary['average_hours'] ?? null;
$stageBreakdown = $summary['stage_breakdown'] ?? [];
?>

<div class="row">
    <div class="col-12">
        <h2>Laporan SLA Entri Nilai</h2>
        <p class="text-muted mb-0">
            Menilai kecepatan entri nilai setelah pelaksanaan seminar/ujian. Batas SLA saat ini: <?= $thresholdHours ?> jam.
        </p>
    </div>
</div>

<?php include __DIR__ . '/_nav.php'; ?>

<?php
$stageOptions = [['value' => '', 'label' => 'Semua Tahap']];
foreach ($stageLabels as $stageKey => $stageLabel) {
    $stageOptions[] = ['value' => $stageKey, 'label' => $stageLabel];
}
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$statusOptions = [['value' => '', 'label' => 'Semua Status']];
foreach (['Belum Diisi', 'Tepat Waktu', 'Lewat SLA'] as $statusOption) {
    $statusOptions[] = ['value' => $statusOption, 'label' => $statusOption];
}
$filterConfig = [
    'id' => 'slaFilterForm',
    'method' => 'GET',
    'action' => '/reports/sla',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'stage',
            'label' => 'Tahap',
            'value' => (string)($selectedStage ?? ''),
            'options' => $stageOptions,
            'col' => 3
        ],
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Angkatan',
            'value' => (string)($selectedAngkatan ?? ''),
            'options' => $angkatanOptions,
            'col' => 3
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'label' => 'Status SLA',
            'value' => (string)($statusFilter ?? ''),
            'options' => $statusOptions,
            'col' => 3
        ],
        [
            'type' => 'search',
            'name' => 'student_search',
            'label' => 'Cari Mahasiswa',
            'placeholder' => 'NIM / Nama',
            'value' => $currentSearch ?? '',
            'col' => 3
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Terapkan', 'icon' => 'fas fa-filter', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/sla'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Total Jadwal</h6>
                <h3 class="mb-0"><?= htmlspecialchars($summary['total_events'] ?? 0) ?></h3>
                <small class="text-muted">Seluruh seminar/ujian terjadwal</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Cakupan Entri</h6>
                <h3 class="mb-0"><?= $coverage ?>%</h3>
                <small class="text-muted">Jadwal dengan nilai yang sudah diinput</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Ketepatan Waktu</h6>
                <h3 class="mb-0"><?= $onTimePercent ?>%</h3>
                <small class="text-muted">Entri berada dalam batas SLA</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Rata-rata Waktu</h6>
                <h3 class="mb-0">
                    <?= $averageHours !== null ? $averageHours . ' jam' : '-' ?>
                </h3>
                <small class="text-muted">Rata-rata jeda entri nilai</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Kinerja per Tahap</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tahap</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Tepat Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stageBreakdown as $stage => $data): ?>
                        <tr>
                            <td><?= htmlspecialchars(ucwords(str_replace('-', ' ', $stage))) ?></td>
                            <td class="text-center"><?= htmlspecialchars($data['total']) ?></td>
                            <td class="text-center">
                                <?= htmlspecialchars($data['on_time']) ?>
                                <?php
                                    $stagePercent = $data['total'] > 0 ? round(($data['on_time'] / $data['total']) * 100, 1) : 0;
                                ?>
                                <span class="text-muted small ms-1"><?= $stagePercent ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <small class="text-muted d-block mt-2">Persentase dihitung dari jadwal yang memiliki entri nilai.</small>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Rincian Jadwal & Entri</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="slaTable">
                        <thead class="table-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Tahap</th>
                                <th>Jadwal</th>
                                <th>Entri Pertama</th>
                                <th class="text-center">Selisih (jam)</th>
                                <th>Status</th>
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
    var table = $('#slaTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '/reports/sla/data',
            data: function(d) {
                <?php if ($selectedStage): ?>
                d.stage = <?= json_encode($selectedStage) ?>;
                <?php endif; ?>
                <?php if ($selectedAngkatan): ?>
                d.angkatan = <?= json_encode($selectedAngkatan) ?>;
                <?php endif; ?>
                <?php if ($statusFilter): ?>
                d.status = <?= json_encode($statusFilter) ?>;
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
    $('#slaFilterForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        table.settings()[0].ajax.data = function(d) {
            d.stage = params.stage || '';
            d.angkatan = params.angkatan || '';
            d.status = params.status || '';
            d.search = params.search || '';
        };
        table.ajax.reload();
    });
});
</script>
