<?php
$students = $students ?? [];
$angkatanList = $angkatanList ?? [];
$currentAngkatan = $currentAngkatan ?? '';
$currentSearch = $currentSearch ?? '';
$error = $error ?? null;

$stageOrder = ['sempro', 'semhas', 'pra-ujian', 'ujian'];
$stageLabels = [
    'sempro' => 'Seminar Proposal',
    'semhas' => 'Seminar Hasil',
    'pra-ujian' => 'Pra-Ujian Skripsi',
    'ujian' => 'Ujian Skripsi'
];

$activeAngkatan = Settings::getActiveAngkatan();
?>

<div class="row mb-3 align-items-center">
    <div class="col-lg-8 col-md-7">
        <h2 class="mb-1"><i class="fas fa-chart-line"></i> Monitoring Tahapan Mahasiswa</h2>
        <p class="text-muted mb-0">Lihat status judul, seminar, dan ujian seluruh mahasiswa secara real-time.</p>
        <?php if (!empty($activeAngkatan)): ?>
        <p class="text-muted small mb-0">Menampilkan data untuk angkatan: <?= implode(', ', array_map('htmlspecialchars', $activeAngkatan)) ?></p>
        <?php else: ?>
        <p class="text-muted small mb-0">Tidak ada angkatan yang diaktifkan. Hanya menampilkan mahasiswa dengan status selain "LULUS".</p>
        <?php endif; ?>
        <p class="text-muted small mb-0">Catatan: Mahasiswa dengan status "LULUS" tidak dihitung dalam statistik ini.</p>
    </div>
    <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
        <span class="badge bg-primary"><?= count($students) ?> mahasiswa</span>
    </div>
</div>

<?php
$filteredAngkatanList = [];
foreach ($angkatanList as $angkatan) {
    if (empty($activeAngkatan) || in_array($angkatan, $activeAngkatan)) {
        $filteredAngkatanList[] = $angkatan;
    }
}
$angkatanOptions = [
    ['value' => '', 'label' => 'Semua Angkatan Aktif']
];
foreach ($filteredAngkatanList as $angkatan) {
    $angkatanOptions[] = [
        'value' => (string)$angkatan,
        'label' => (string)$angkatan
    ];
}
$filterConfig = [
    'id' => 'studentProgressFilter',
    'method' => 'GET',
    'action' => '/students/progress',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Filter Angkatan',
            'value' => (string)$currentAngkatan,
            'options' => $angkatanOptions,
            'col' => 3
        ],
        [
            'type' => 'search',
            'name' => 'search',
            'label' => 'Cari Mahasiswa',
            'placeholder' => 'NIM atau nama...',
            'value' => $currentSearch ?? '',
            'col' => 4
        ],
    ],
    'actions' => array_values(array_filter([
        ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/students/progress'],
        !empty($activeAngkatan) ? ['type' => 'link', 'label' => 'Pengaturan', 'icon' => 'fas fa-cog', 'variant' => 'outline-primary', 'url' => '/settings'] : null,
    ]))
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($students)): ?>
<div class="alert alert-info">
    Belum ada data mahasiswa untuk ditampilkan.
 </div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="progressTable">
                <thead class="table-light">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Angkatan</th>
                        <th>Status Judul</th>
                        <?php foreach ($stageOrder as $stageKey): ?>
                        <th><?= htmlspecialchars($stageLabels[$stageKey]) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($student['nim']) ?></strong><br>
                            <?= htmlspecialchars($student['name']) ?><br>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($student['student_status']) ?></span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($student['angkatan'] ?? '-') ?></span>
                        </td>
                        <td>
                            <span class="badge <?= htmlspecialchars($student['title']['badge']) ?>"><?= htmlspecialchars($student['title']['label']) ?></span><br>
                            <small class="text-muted"><?= htmlspecialchars($student['title']['detail']) ?></small>
                        </td>
                        <?php
                        $stageMap = [];
                        foreach ($student['stages'] as $stageInfo) {
                            $stageMap[$stageInfo['key']] = $stageInfo['status'];
                        }
                        ?>
                        <?php foreach ($stageOrder as $stageKey): ?>
                            <?php $status = $stageMap[$stageKey] ?? ['label' => '-', 'badge' => 'bg-secondary', 'detail' => '']; ?>
                            <td>
                                <span class="badge <?= htmlspecialchars($status['badge']) ?>">
                                    <?= htmlspecialchars($status['label']) ?>
                                </span><br>
                                <small class="text-muted"><?= htmlspecialchars($status['detail']) ?></small>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="text-muted small mt-2">
            Keterangan: warna badge menunjukkan urgensi. Hijau = nilai sudah diinput, Biru = selesai, Kuning = menunggu nilai, Abu = belum terjadwal.
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $('#progressTable').length) {
        $('#progressTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            pageLength: 25,
            order: [[0, 'asc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function(settings) {
                // Update badge count after DataTable filtering
                var api = this.api();
                var pageInfo = api.page.info();
                $('.badge.bg-primary').text(pageInfo.recordsDisplay + ' mahasiswa');
            },
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ]
        });
    }
});
</script>

<style>
/* Memperbaiki tampilan dropdown "Show entries" */
.dataTables_length select {
    width: auto !important;
    min-width: 80px;
    padding-right: 30px !important;
}
</style>
