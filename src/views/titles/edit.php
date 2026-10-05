<?php
$titleData = $title ?? [];
$error = $error ?? null;
$success = $success ?? null;
$lecturerOptions = $lecturerOptions ?? [];
$currentAssignments = $currentAssignments ?? [];
$activeRole = strtolower($_SESSION['active_role'] ?? ($_SESSION['role'] ?? ''));
$canVerify = in_array($activeRole, ['kombi', 'superadmin'], true);
$backUrl = $canVerify ? '/titles/view' : '/dashboard';
$roleMeta = $roleMeta ?? [
    'pembimbing_1' => ['label' => 'Pembimbing 1', 'required' => true],
    'pembimbing_2' => ['label' => 'Pembimbing 2', 'required' => false],
    'penguji_1' => ['label' => 'Penguji Ketua', 'required' => true],
    'penguji_2' => ['label' => 'Penguji Anggota 1', 'required' => false],
    'penguji_3' => ['label' => 'Penguji Anggota 2', 'required' => false],
];
$letterLinks = $letterLinks ?? [];
$roleLabels = [];
foreach ($roleMeta as $roleKey => $meta) {
    $roleLabels[$roleKey] = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
}
?>

<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2>Edit Pengajuan Judul</h2>
            <p class="text-muted mb-0">Perbarui judul skripsi milik mahasiswa. Setiap perubahan akan mengulang proses verifikasi.</p>
        </div>
        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-pen-to-square me-2"></i> Formulir Pengubahan</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-4">
                    <dt class="col-sm-4">Mahasiswa</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars(($titleData['nim'] ?? '') . ' - ' . ($titleData['student_name'] ?? '')) ?></dd>

                    <dt class="col-sm-4">Status Saat Ini</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-secondary"><?= htmlspecialchars($titleData['status'] ?? '-') ?></span>
                        <span class="text-muted small d-block">Status akan direset menjadi MENUNGGU setelah disimpan.</span>
                    </dd>
                </dl>

                <form method="POST" action="/titles/<?= htmlspecialchars($titleData['id'] ?? '') ?>/update">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Skripsi *</label>
                        <input type="text"
                               class="form-control"
                               id="title"
                               name="title"
                               required
                               value="<?= htmlspecialchars($titleData['title'] ?? '') ?>">
                    </div>

                    <?php if (!empty($currentAssignments)): ?>
                    <div class="mb-3">
                        <label class="form-label">Penetapan Dosen Aktif</label>
                        <div class="border rounded p-3">
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($currentAssignments as $role => $assignment): ?>
                                    <li class="mb-1">
                                        <strong><?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?>:</strong>
                                        <?= htmlspecialchars($assignment['name'] ?? '-') ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($lecturerOptions)): ?>
                        <div class="mb-3">
                            <label class="form-label">Usulan Dosen dan Link Surat Tugas</label>
                            <?php foreach ($roleMeta as $roleKey => $meta): ?>
                                <?php
                                    $roleLabel = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
                                    $selectedValue = $titleData['proposed_' . $roleKey . '_id'] ?? ($currentAssignments[$roleKey]['id'] ?? '');
                                    $isRequired = !empty($meta['required']);
                                    $letterField = 'letter_link_' . $roleKey;
                                    $linkValue = $letterLinks[$roleKey] ?? '';
                                ?>
                                <div class="mb-3 border rounded p-3">
                                    <label for="<?= $roleKey ?>_id" class="form-label"><?= htmlspecialchars($roleLabel) ?><?= $isRequired ? ' *' : '' ?></label>
                                    <select class="form-select"
                                            id="<?= $roleKey ?>_id"
                                            name="<?= $roleKey ?>_id"
                                            <?= $isRequired ? 'required' : '' ?>>
                                        <option value=""><?= $isRequired ? 'Pilih ' . $roleLabel : 'Opsional' ?></option>
                                        <?php foreach ($lecturerOptions as $lecturer): ?>
                                            <option value="<?= $lecturer['id'] ?>" <?= ((string)$selectedValue === (string)$lecturer['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lecturer['name']) ?> (<?= htmlspecialchars($lecturer['role']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="<?= $letterField ?>" class="form-label mt-3">Link Surat Tugas <?= htmlspecialchars($roleLabel) ?><?= $isRequired ? ' *' : '' ?></label>
                                    <input type="url"
                                           class="form-control"
                                           id="<?= $letterField ?>"
                                           name="<?= $letterField ?>"
                                           placeholder="https://drive.google.com/..."
                                           <?= $isRequired ? 'required' : '' ?>
                                           value="<?= htmlspecialchars($linkValue) ?>">
                                    <?php if (!$isRequired): ?>
                                        <div class="form-text">Isi bila ada dosen yang diusulkan.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Daftar dosen internal belum tersedia. Tambahkan dosen terlebih dahulu.</div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="/titles/view" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-trash-alt me-2"></i> Hapus Pengajuan</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">Hapus judul ini bila mahasiswa mengajukan ulang atau terjadi kesalahan data.</p>
                <form method="POST" action="/titles/<?= htmlspecialchars($titleData['id'] ?? '') ?>/delete" onsubmit="return confirm('Yakin ingin menghapus pengajuan judul ini? Tindakan ini tidak dapat dibatalkan.');">
                    <?= Csrf::field(); ?>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash"></i> Hapus Judul
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
