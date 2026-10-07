<?php
$summary = $summary ?? [];
$students = $students ?? [];
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
$statusFilter = $statusFilter ?? null;
$statusList = $statusList ?? [];
?>

<div class="row">
    <div class="col-12">
        <h2>Laporan Masa Studi</h2>
        <p class="text-muted">Menghitung lama studi mahasiswa berdasarkan selisih semester masuk dan lulus setelah dikurangi semester cuti resmi.</p>
    </div>
</div>

<?php
$angkatanOptions = [['value' => '', 'label' => 'Semua Angkatan']];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = ['value' => (string)$angkatan, 'label' => (string)$angkatan];
}
$statusOptions = [['value' => '', 'label' => 'Semua Status']];
foreach ($statusList as $status) {
    $statusOptions[] = ['value' => (string)$status, 'label' => (string)$status];
}
$filterConfig = [
    'id' => 'studyPeriodFilter',
    'method' => 'GET',
    'action' => '/reports/study-period',
    'fields' => [
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
            'label' => 'Status Mahasiswa',
            'value' => (string)($statusFilter ?? ''),
            'options' => $statusOptions,
            'col' => 3
        ],
        [
            'type' => 'search',
            'name' => 'student_search',
            'label' => 'Cari NIM / Nama',
            'placeholder' => 'Contoh: 19xxxx / Nama mahasiswa',
            'value' => $currentSearch ?? '',
            'col' => 4
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Terapkan', 'icon' => 'fas fa-filter', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/study-period'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Ringkasan per Angkatan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="studyPeriodSummaryTable">
                        <thead class="table-light">
                            <tr>
                                <th>Angkatan</th>
                                <th>Rata-rata Masa Studi (Semester)</th>
                                <th>Jumlah Lulus</th>
                                <th>Total Mahasiswa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($summary)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data masa studi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($summary as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['angkatan']) ?></td>
                                        <td><?= $row['average_semester'] !== null ? number_format($row['average_semester'], 2) : '-' ?></td>
                                        <td><?= htmlspecialchars($row['lulusan']) ?></td>
                                        <td><?= htmlspecialchars($row['total_mahasiswa']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="studyPeriodDetailTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Angkatan</th>
                                <th>Semester Masuk</th>
                                <th>Semester Lulus</th>
                                <th>Cuti (Semester)</th>
                                <th>Masa Studi (Semester)</th>
                                <th>Status</th>
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
$(document).ready(function() {
    $('#studyPeriodSummaryTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        language: {
            url: "/public/vendor/datatables/1.13.4/i18n/id.json"
        }
    });

    var table = $('#studyPeriodDetailTable').DataTable({
        language: {
            url: "/public/vendor/datatables/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '/reports/study-period/data',
            data: function(d) {
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
            { data: 5 },
            { data: 6 },
            { data: 7 }
        ]
    });
    
    // Update AJAX request when filter form is submitted
    $('#studyPeriodFilter').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        table.settings()[0].ajax.data = function(d) {
            d.angkatan = params.angkatan || '';
            d.status = params.status || '';
            d.search = params.search || '';
        };
        table.ajax.reload();
    });
});
</script>
