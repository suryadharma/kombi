<?php
$history = $history ?? [];
$filters = $filters ?? ['student_id' => null, 'date_from' => null, 'date_to' => null];
$student = $student ?? null;
$roleLabels = [
    'pembimbing_1' => 'Pembimbing 1',
    'pembimbing_2' => 'Pembimbing 2',
    'penguji_1' => 'Ketua Penguji',
    'penguji_2' => 'Penguji Anggota 1',
    'penguji_3' => 'Penguji Anggota 2'
];
?>

<div class="row">
    <div class="col-12">
        <h2>Riwayat Pergantian Dosen</h2>
        <?php if ($student): ?>
            <p>Riwayat pergantian dosen untuk: <strong><?= htmlspecialchars($student['name']) ?>
                    (<?= htmlspecialchars($student['nim']) ?>)</strong></p>
        <?php else: ?>
            <p>Daftar riwayat pergantian pembimbing dan penguji untuk mahasiswa</p>
        <?php endif; ?>
    </div>
</div>

<?php
$filterFields = [];
if (!$student) {
    $studentOptions = [['value' => '', 'label' => 'Semua Mahasiswa']];
    foreach ($students as $s) {
        $studentOptions[] = [
            'value' => (string)$s['id'],
            'label' => $s['nim'] . ' - ' . $s['name']
        ];
    }
    $filterFields[] = [
        'type' => 'select',
        'name' => 'student',
        'label' => 'Mahasiswa',
        'value' => (string)($filters['student_id'] ?? ''),
        'options' => $studentOptions,
        'col' => 3
    ];
}
$dateCol = $student ? 4 : 3;
$filterFields[] = [
    'type' => 'date',
    'name' => 'date_from',
    'label' => 'Dari Tanggal',
    'value' => $filters['date_from'] ?? '',
    'col' => $dateCol
];
$filterFields[] = [
    'type' => 'date',
    'name' => 'date_to',
    'label' => 'Sampai Tanggal',
    'value' => $filters['date_to'] ?? '',
    'col' => $dateCol
];
$actionButtons = [
    ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
    ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/assignments/history' . ($student ? '/' . $student['id'] : '')],
];
if ($student) {
    $actionButtons[] = ['type' => 'link', 'label' => 'Kembali ke Profil', 'icon' => 'fas fa-user', 'variant' => 'secondary', 'url' => '/students/' . $student['id']];
} else {
    $actionButtons[] = ['type' => 'link', 'label' => 'Buat Penetapan Baru', 'icon' => 'fas fa-plus', 'variant' => 'success', 'url' => '/assignments/set'];
}
$filterConfig = [
    'id' => 'assignmentHistoryFilter',
    'method' => 'GET',
    'action' => '/assignments/history' . ($student ? '/' . $student['id'] : ''),
    'fields' => $filterFields,
    'actions' => $actionButtons
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Riwayat Pergantian</h5>
                <small class="text-muted">Urutan terbaru ditampilkan paling atas.</small>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada riwayat pergantian yang sesuai dengan filter saat ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal Efektif</th>
                                    <?php if (!$student): ?>
                                        <th>Mahasiswa</th>
                                    <?php endif; ?>
                                    <th>Role</th>
                                    <th>Dosen Lama</th>
                                    <th>Dosen Baru</th>
                                    <th>Alasan</th>
                                    <th>Dilakukan Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $row): ?>
                                    <?php $roleLabel = $roleLabels[$row['role']] ?? ucfirst(str_replace('_', ' ', $row['role'])); ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['effective_date']); ?></td>
                                        <?php if (!$student): ?>
                                            <td>
                                                <a href="/students/<?= htmlspecialchars($row['student_id']); ?>">
                                                    <?= htmlspecialchars($row['student_nim'] ?? '-'); ?> -
                                                    <?= htmlspecialchars($row['student_name'] ?? '-'); ?>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold"><?= htmlspecialchars($roleLabel); ?></span>
                                                <small class="text-muted">Penetapan yang diganti</small>
                                            </div>
                                        </td>
                    <td>
                        <div class="small text-muted mb-1">
                            <?= htmlspecialchars($roleLabel); ?> &mdash; Dosen Lama
                        </div>
                        <del class="text-danger fw-semibold">
                            <?= htmlspecialchars($row['lecturer_old_name'] ?? '-'); ?>
                        </del>
                        <?php if (!empty($row['letter_url_old'])): ?>
                            <div><a href="<?= htmlspecialchars($row['letter_url_old']) ?>" target="_blank" rel="noopener">Surat Tugas Lama</a></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="small text-muted mb-1">
                            <?= htmlspecialchars($roleLabel); ?> &mdash; Dosen Baru
                        </div>
                        <strong class="text-success">
                            <?= htmlspecialchars($row['lecturer_new_name'] ?? '-'); ?>
                        </strong>
                        <?php if (!empty($row['letter_url_new'])): ?>
                            <div><a href="<?= htmlspecialchars($row['letter_url_new']) ?>" target="_blank" rel="noopener">Surat Tugas Baru</a></div>
                        <?php endif; ?>
                    </td>
                                        <td><?= htmlspecialchars($row['reason'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($row['assigned_by_name'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for student dropdown
    if (typeof $ !== 'undefined') {
        $('#assignmentHistoryFilter_student').select2({
            placeholder: 'Cari dan pilih mahasiswa...',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
