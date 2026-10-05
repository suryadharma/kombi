<?php
$components = isset($components) ? $components : [];
?>

<div class="row">
    <div class="col-12">
        <h2>Komponen Penilaian</h2>
        <p>Kelola komponen penilaian untuk berbagai tahap seminar dan ujian</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <a href="/components/create" class="btn btn-primary">Tambah Komponen</a>
    </div>
</div>

<?php
$stageValue = $_GET['stage'] ?? '';
$stageOptions = [
    ['value' => '', 'label' => 'Semua Tahap'],
    ['value' => 'sempro', 'label' => 'Seminar Proposal'],
    ['value' => 'semhas', 'label' => 'Seminar Hasil'],
    ['value' => 'pra-ujian', 'label' => 'Pra-Ujian Skripsi'],
    ['value' => 'ujian', 'label' => 'Ujian Skripsi'],
];
$filterConfig = [
    'id' => 'componentFilterForm',
    'method' => 'GET',
    'action' => '/components',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'stage',
            'label' => 'Filter Tahap',
            'value' => (string)$stageValue,
            'options' => $stageOptions,
            'col' => 3
        ]
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/components'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Daftar Komponen Penilaian</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="componentsTable">
                        <thead>
                            <tr>
                                <th>Nama Komponen</th>
                                <th>Tahap</th>
                                <th>Bobot</th>
                                <th>Urutan</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($components)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data komponen</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($components as $component): ?>
                            <tr>
                                <td><?= htmlspecialchars($component['name']) ?></td>
                                <td>
                                    <?php
                                    switch ($component['stage']) {
                                        case 'sempro':
                                            echo 'Seminar Proposal';
                                            break;
                                        case 'semhas':
                                            echo 'Seminar Hasil';
                                            break;
                                        case 'pra-ujian':
                                            echo 'Pra-Ujian Skripsi';
                                            break;
                                        case 'ujian':
                                            echo 'Ujian Skripsi';
                                            break;
                                        default:
                                            echo htmlspecialchars($component['stage']);
                                    }
                                    ?>
                                </td>
                                <td><?= (floatval($component['weight']) * 100) ?>%</td>
                                <td><?= htmlspecialchars($component['sort_order']) ?></td>
                                <td><?= !empty($component['description']) ? htmlspecialchars($component['description']) : '-' ?></td>
                                <td>
                                    <a href="/components/<?= $component['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="/components/<?= $component['id'] ?>/delete" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus komponen ini?')">Hapus</a>
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
