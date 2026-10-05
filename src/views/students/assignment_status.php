<?php
$students = $students ?? [];
$roleLabels = $roleLabels ?? [];
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$summary = $summary ?? [];
$error = $error ?? null;
$currentSearch = $currentSearch ?? '';
$includeLulus = $includeLulus ?? false;

$activeAngkatan = Settings::getActiveAngkatan();
?>

<div class="row">
    <div class="col-12">
        <h2>Monitoring Penetapan Pembimbing & Penguji</h2>
        <p class="text-muted mb-0">Gunakan filter angkatan untuk menemukan mahasiswa yang belum memiliki judul ataupun penetapan tim lengkap.</p>
        <?php if (!empty($activeAngkatan)): ?>
        <p class="text-muted small mb-0">Menampilkan data untuk angkatan: <?= implode(', ', array_map('htmlspecialchars', $activeAngkatan)) ?></p>
        <?php else: ?>
        <p class="text-muted small mb-0">Tidak ada angkatan yang diaktifkan. Menampilkan seluruh angkatan yang tersedia.</p>
        <?php endif; ?>
        <?php if ($includeLulus): ?>
            <p class="text-muted small mb-0">Catatan: Mahasiswa berstatus "LULUS" turut ditampilkan untuk keperluan pelacakan dan arsip.</p>
        <?php else: ?>
            <p class="text-muted small mb-0">Catatan: Mahasiswa dengan status "LULUS" tidak ikut ditampilkan. Centang opsi di bawah untuk menyertakan arsip lulusan.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

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
ob_start(); ?>
<div class="form-check">
    <input class="form-check-input" type="checkbox" value="1" id="includeLulus" name="include_lulus" <?= $includeLulus ? 'checked' : '' ?>>
    <label class="form-check-label" for="includeLulus">
        Sertakan mahasiswa dengan status <span class="badge bg-success text-white">LULUS</span> dalam daftar
    </label>
</div>
<?php
$filterExtra = ob_get_clean();
$filterConfig = [
    'id' => 'assignmentStatusFilter',
    'method' => 'GET',
    'action' => '/students/assignment-status',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Filter Angkatan',
            'value' => (string)($selectedAngkatan ?? ''),
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
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/students/assignment-status'],
        !empty($activeAngkatan) ? ['type' => 'link', 'label' => 'Pengaturan', 'icon' => 'fas fa-cog', 'variant' => 'outline-primary', 'url' => '/settings'] : null,
    ])),
    'extra' => $filterExtra
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <h6 class="card-title">Legenda</h6>
                <div class="d-flex flex-wrap gap-3">
                    <span class="badge bg-success">Sudah ditetapkan</span>
                    <span class="badge bg-danger">Belum ditetapkan</span>
                    <span class="badge bg-info">Menunggu verifikasi</span>
                    <span class="badge bg-warning text-dark">Perlu perhatian</span>
                    <span class="badge bg-secondary">Belum mengajukan</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($summary)): ?>
<div class="card mt-3">
    <div class="card-header bg-light">
        <h5 class="mb-0">Ringkasan per Angkatan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Angkatan</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Belum Ajukan Judul</th>
                        <th class="text-center">Judul Pending</th>
                        <th class="text-center">Judul Disetujui</th>
                        <th class="text-center">Belum Punya Pembimbing</th>
                        <th class="text-center">Belum Punya Penguji Lengkap</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $angkatan => $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['angkatan']) ?></strong></td>
                        <td class="text-center"><?= htmlspecialchars($row['total']) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $row['no_title'] > 0 ? 'bg-danger' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($row['no_title']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $row['title_pending'] > 0 ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($row['title_pending']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                <?= htmlspecialchars($row['title_approved']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $row['missing_pembimbing'] > 0 ? 'bg-danger' : 'bg-success' ?>">
                                <?= htmlspecialchars($row['missing_pembimbing']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $row['missing_penguji'] > 0 ? 'bg-danger' : 'bg-success' ?>">
                                <?= htmlspecialchars($row['missing_penguji']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($students)): ?>
<div class="alert alert-info mt-3">
    <?php if ($selectedAngkatan || $currentSearch): ?>
        Tidak ada mahasiswa yang sesuai dengan filter yang diterapkan.
    <?php else: ?>
        Belum ada data mahasiswa untuk ditampilkan.
    <?php endif; ?>
</div>
<?php else: ?>
<div class="card mt-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="assignmentStatusTable">
                <thead class="table-light">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Status Judul</th>
                        <?php foreach ($roleLabels as $roleKey => $label): ?>
                        <th><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $row): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($row['student']['nim']) ?></strong><br>
                            <?= htmlspecialchars($row['student']['name']) ?><br>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($row['student']['angkatan']) ?></span>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($row['student']['status']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= htmlspecialchars($row['title']['badge']) ?>"><?= htmlspecialchars($row['title']['label']) ?></span><br>
                            <small class="text-muted"><?= htmlspecialchars($row['title']['detail']) ?></small>
                        </td>
                        <?php foreach ($roleLabels as $roleKey => $label): ?>
                        <td>
                            <span class="badge <?= htmlspecialchars($row['assignment'][$roleKey]['badge']) ?>">
                                <?= htmlspecialchars($row['assignment'][$roleKey]['label']) ?>
                            </span>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $('#assignmentStatusTable').length) {
        // Nonaktifkan peringatan DataTables
        $.fn.dataTable.ext.errMode = 'none';
        
        $('#assignmentStatusTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            pageLength: 25,
            order: [[0, 'asc']],
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
            columnDefs: [
                { width: "25%", targets: 0 },
                { width: "25%", targets: 1 }
            ],
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
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
