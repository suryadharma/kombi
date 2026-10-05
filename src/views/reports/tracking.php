<?php
$students = $students ?? [];
$summary = $summary ?? [];
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
$slaStatusClassMap = [
    'Dalam Batas' => 'bg-success',
    'Lewat SLA' => 'bg-danger',
    'Belum Lengkap' => 'bg-secondary',
    'Tanpa Target' => 'bg-dark'
];
?>

<div class="row">
    <div class="col-12">
        <h2>Laporan Pelacakan Tugas Akhir</h2>
        <p class="text-muted">Memantau durasi pengerjaan tugas akhir mahasiswa dari Seminar Proposal hingga Ujian Skripsi.</p>
    </div>
</div>

<?php include __DIR__ . '/_nav.php'; ?>

<?php
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$filterConfig = [
    'id' => 'trackingFilterForm',
    'method' => 'GET',
    'action' => '/reports/tracking',
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
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/tracking'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<?php if (!empty($summary) && isset($summary['total_students'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan Pelacakan</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1"><?= htmlspecialchars($summary['total_students']) ?></h4>
                            <small class="text-muted">Total Mahasiswa</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1 text-info"><?= htmlspecialchars($summary['has_sempro']) ?></h4>
                            <small class="text-muted">Sudah Sempro</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1 text-primary"><?= htmlspecialchars($summary['has_semhas']) ?></h4>
                            <small class="text-muted">Sudah Semhas</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 border rounded">
                            <h4 class="mb-1 text-success"><?= htmlspecialchars($summary['has_ujian']) ?></h4>
                            <small class="text-muted">Sudah Ujian</small>
                        </div>
                    </div>
                </div>
                <?php if ($summary['avg_duration_days'] !== null): ?>
                <div class="text-center mt-3">
                    <h5 class="mb-1">
                        <span class="badge bg-primary">Rata-rata Durasi: <?= htmlspecialchars($summary['avg_duration_days']) ?> hari</span>
                    </h5>
                    <small class="text-muted">Dari Seminar Proposal hingga Ujian Skripsi</small>
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
                <h5 class="mb-0">Detail Pelacakan Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="trackingTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Angkatan</th>
                                <th>Status</th>
                                <th>Sempro</th>
                                <th>Semhas</th>
                                <th>Pra-Ujian</th>
                                <th>Ujian</th>
                                <th>Durasi Sempro-Semhas</th>
                                <th>Durasi Semhas-Pra-Ujian</th>
                                <th>Durasi Pra-Ujian-Ujian</th>
                                <th>Durasi Sempro-Ujian</th>
                                <th>Durasi Semhas-Ujian</th>
                                <th>Pembimbing</th>
                                <th>Penguji</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="15" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initTrackingTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            setTimeout(initTrackingTable, 100);
            return;
        }
        
        $(document).ready(function() {
            var table = $('#trackingTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
                },
                processing: true,
                serverSide: true,
                pageLength: 25,
                order: [[0, 'asc']],
                ajax: {
                    url: '/reports/tracking/data',
                    data: function(d) {
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
                    { data: 4, orderable: false },
                    { data: 5, orderable: false },
                    { data: 6, orderable: false },
                    { data: 7, orderable: false },
                    { data: 8, orderable: false },
                    { data: 9, orderable: false },
                    { data: 10, orderable: false },
                    { data: 11, orderable: false },
                    { data: 12, orderable: false },
                    { data: 13, orderable: false },
                    { data: 14, orderable: false }
                ]
            });
            
            // Update AJAX request when filter form is submitted
            $('#trackingFilterForm').on('submit', function(e) {
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
    }
    
    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrackingTable);
    } else {
        initTrackingTable();
    }
})();
</script>
