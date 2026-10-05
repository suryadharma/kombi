<?php
$proposedLecturers = $proposedLecturers ?? [];
$letterLinks = $letterLinks ?? ['advisor' => [], 'examiner' => []];
$roleMeta = $roleMeta ?? [
    'pembimbing_1' => ['label' => 'Pembimbing 1'],
    'pembimbing_2' => ['label' => 'Pembimbing 2'],
    'penguji_1' => ['label' => 'Penguji Ketua'],
    'penguji_2' => ['label' => 'Penguji Anggota 1'],
    'penguji_3' => ['label' => 'Penguji Anggota 2'],
];
$roleLabels = [];
foreach ($roleMeta as $roleKey => $meta) {
    $roleLabels[$roleKey] = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
}
$activeRole = strtolower($_SESSION['active_role'] ?? ($_SESSION['role'] ?? ''));
$canVerify = in_array($activeRole, ['kombi', 'superadmin'], true);
$isWaiting = isset($title['status']) && $title['status'] === 'MENUNGGU';
$backUrl = $canVerify ? '/titles/view' : '/dashboard';
?>

<div class="row">
    <div class="col-12">
        <h2>Detail Judul Skripsi</h2>
        <p>Informasi lengkap judul skripsi</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Judul</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">NIM</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($title['nim'] ?? '') ?></dd>
                    
                    <dt class="col-sm-3">Nama Mahasiswa</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($title['student_name'] ?? '') ?></dd>
                    
                    <dt class="col-sm-3">Tanggal Pengajuan</dt>
                    <dd class="col-sm-9"><?= date('d M Y H:i', strtotime($title['submitted_at'])) ?></dd>
                    
                    <?php if ($title['verified_at']): ?>
                    <dt class="col-sm-3">Tanggal Verifikasi</dt>
                    <dd class="col-sm-9"><?= date('d M Y H:i', strtotime($title['verified_at'])) ?></dd>
                    <?php endif; ?>
                    
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        <?php
                        switch ($title['status']) {
                            case 'MENUNGGU':
                                echo '<span class="badge bg-warning">Menunggu</span>';
                                break;
                            case 'DITERIMA':
                                echo '<span class="badge bg-success">Diterima</span>';
                                break;
                            case 'DITOLAK':
                                echo '<span class="badge bg-danger">Ditolak</span>';
                                break;
                            case 'PERLU_REVISI':
                                echo '<span class="badge bg-info">Perlu Revisi</span>';
                                break;
                            default:
                                echo '<span class="badge bg-secondary">' . htmlspecialchars($title['status']) . '</span>';
                        }
                        ?>
                    </dd>
                    
                    <?php if ($title['verified_by_name']): ?>
                    <dt class="col-sm-3">Diverifikasi Oleh</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($title['verified_by_name']) ?></dd>
                    <?php endif; ?>
                    
                    <dt class="col-sm-3">Judul</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($title['title'] ?? '') ?></dd>
                    
                    <dt class="col-sm-3">Surat Tugas Pembimbing</dt>
                    <dd class="col-sm-9">
                        <?php if (!empty($letterLinks['advisor'])): ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($letterLinks['advisor'] as $roleKey => $link): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($roleLabels[$roleKey] ?? ucfirst($roleKey)) ?>:</strong>
                                        <a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener">Lihat Surat</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span class="text-muted">Tidak tersedia</span>
                        <?php endif; ?>
                    </dd>
                    
                    <dt class="col-sm-3">Surat Tugas Penguji</dt>
                    <dd class="col-sm-9">
                        <?php if (!empty($letterLinks['examiner'])): ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($letterLinks['examiner'] as $roleKey => $link): ?>
                                    <li>
                                        <strong><?= htmlspecialchars($roleLabels[$roleKey] ?? ucfirst($roleKey)) ?>:</strong>
                                        <a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener">Lihat Surat</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span class="text-muted">Tidak tersedia</span>
                        <?php endif; ?>
                    </dd>
                    
                    <?php if (!empty($proposedLecturers)): ?>
                        <?php foreach ($proposedLecturers as $role => $data): ?>
                            <dt class="col-sm-3"><?= htmlspecialchars($data['label']) ?></dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($data['name'] ?? '-') ?></dd>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($title['notes']): ?>
                    <dt class="col-sm-3">Catatan Verifikasi</dt>
                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($title['notes'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <?php if ($canVerify && $isWaiting): ?>
                        <a href="/titles/verify/<?= urlencode((string)($title['id'] ?? '')) ?>" class="btn btn-warning">
                            <i class="fas fa-check-circle"></i> Verifikasi Judul
                        </a>
                    <?php endif; ?>
                    <?php if ($canVerify): ?>
                        <a href="/titles/<?= urlencode((string)($title['id'] ?? '')) ?>/edit" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Judul
                        </a>
                    <?php endif; ?>
                    <?php if ($canVerify): ?>
                        <form method="POST" action="/titles/<?= htmlspecialchars($title['id'] ?? '') ?>/delete" onsubmit="return confirmDelete();">
                            <?= Csrf::field(); ?>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Hapus Judul
                            </button>
                            <small class="text-muted d-block mt-1 text-center">
                                Menghapus judul dan semua penetapan dosen
                            </small>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    var studentName = '<?= htmlspecialchars($title['student_name'] ?? '') ?>';
    var titleText = '<?= htmlspecialchars(substr($title['title'] ?? '', 0, 50)) ?>';
    
    var message = '⚠️ KONFIRMASI HAPUS JUDUL ⚠️\n\n';
    message += 'Anda akan menghapus:\n';
    message += '• Mahasiswa: ' + studentName + '\n';
    message += '• Judul: ' + (titleText.length > 50 ? titleText + '...' : titleText) + '\n\n';
    message += 'Tindakan ini akan:\n';
    message += '✗ Menghapus judul skripsi\n';
    message += '✗ Menghapus SEMUA penetapan dosen (pembimbing & penguji)\n';
    message += '✗ Menghapus history penetapan\n';
    message += '✗ Tindakan TIDAK DAPAT dibatalkan!\n\n';
    message += 'Lanjutkan menghapus?';
    
    return confirm(message);
}
</script>
