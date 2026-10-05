<?php
$titles = $titles ?? [];
$error = $error ?? null;
$angkatanList = $angkatanList ?? [];
$currentAngkatan = $currentAngkatan ?? '';
$currentSearch = $currentSearch ?? '';
?>

<div class="row">
    <div class="col-12">
        <h2>Verifikasi Judul Skripsi</h2>
        <p>Daftar judul skripsi yang perlu diverifikasi</p>
    </div>
</div>

<?php
$angkatanOptions = [
    ['value' => '', 'label' => 'Semua ' . (!empty($angkatanList) ? 'Angkatan Aktif' : 'Angkatan')]
];
foreach ($angkatanList as $angkatan) {
    $angkatanOptions[] = [
        'value' => (string)$angkatan,
        'label' => (string)$angkatan
    ];
}
$filterConfig = [
    'id' => 'titleVerifyFilter',
    'method' => 'GET',
    'action' => '/titles/verify',
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
        ]
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/titles/verify'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Daftar Judul Menunggu Verifikasi</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="verifyTable">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Angkatan</th>
                                <th>Judul</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($titles)): ?>
                            <tr>
                                <td><span class="text-muted">-</span></td>
                                <td><span class="text-muted">-</span></td>
                                <td><span class="text-muted">-</span></td>
                                <td colspan="3" class="text-center">Tidak ada judul yang perlu diverifikasi</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($titles as $title): ?>
                            <tr>
                                <td><?= htmlspecialchars($title['nim']) ?></td>
                                <td><?= htmlspecialchars($title['student_name']) ?></td>
                                <td><?= htmlspecialchars($title['angkatan']) ?></td>
                                <td><?= htmlspecialchars(substr($title['title'], 0, 50)) ?>...</td>
                                <td><?= date('d M Y H:i', strtotime($title['submitted_at'])) ?></td>
                                <td>
                                    <a href="/titles/verify/<?= $title['id'] ?>" class="btn btn-sm btn-primary">Verifikasi</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $('#verifyTable').length) {
        // Nonaktifkan peringatan DataTables
        $.fn.dataTable.ext.errMode = 'none';
        
        var verifyTable = $('#verifyTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            pageLength: 25,
            order: [[4, 'desc']],
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
            columnDefs: [
                { width: "15%", targets: 0 },
                { width: "20%", targets: 1 },
                { width: "10%", targets: 2 },
                { width: "30%", targets: 3 },
                { width: "15%", targets: 4 },
                { width: "10%", targets: 5 }
            ],
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
        
        // Handle DataTables errors
        verifyTable.on('error.dt', function (e, settings, techNote, message) {
            console.log('DataTables error: ', message);
        });
    }
});
</script>
