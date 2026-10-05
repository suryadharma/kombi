<?php
$students = $students ?? [];
$roleLabels = $roleLabels ?? [];
$currentAssignments = $currentAssignments ?? [];
$selectedStudentId = $selectedStudentId ?? null;
$errors = $errors ?? [];
$success = $success ?? null;
$reason = $reason ?? '';
$selectedRoles = $selectedRoles ?? [];
?>

<div class="row">
    <div class="col-12">
        <h2>Batalkan Penetapan Pembimbing & Penguji</h2>
        <p class="text-muted mb-4">
            Gunakan halaman ini untuk membatalkan penetapan pembimbing atau penguji yang sudah dibuat. Pembatalan akan menghapus penetapan aktif dan dicatat pada log audit.
        </p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pilih Mahasiswa</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/assignments/cancel">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Mahasiswa</label>
                        <select class="form-select select2-student" id="student_id" name="student">
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']); ?>"
                                    <?= ($selectedStudentId && (int)$selectedStudentId === (int)$student['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($student['nim']); ?> - <?= htmlspecialchars($student['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Muat Penetapan</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Petunjuk</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Pilih mahasiswa untuk melihat penetapan yang aktif.</li>
                    <li>Centang penetapan yang ingin dibatalkan kemudian isi alasan (opsional).</li>
                    <li>Pembatalan tidak dapat di-undo; Anda perlu menetapkan ulang jika diperlukan.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Penetapan Aktif</h5>
            </div>
            <div class="card-body">
                <?php if (!$selectedStudentId): ?>
                    <p class="text-muted mb-0">Pilih mahasiswa terlebih dahulu untuk menampilkan penetapan yang dapat dibatalkan.</p>
                <?php elseif (empty($currentAssignments)): ?>
                    <div class="alert alert-info mb-0">
                        Mahasiswa ini belum memiliki penetapan pembimbing maupun penguji yang aktif.
                    </div>
                <?php else: ?>
                    <form method="POST" action="/assignments/cancel">
                        <?= Csrf::field(); ?>
                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($selectedStudentId); ?>">

                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">Pilih</th>
                                        <th style="width: 25%;">Peran</th>
                                        <th style="width: 30%;">Dosen</th>
                                        <th style="width: 20%;">Tgl Efektif</th>
                                        <th style="width: 20%;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roleLabels as $roleKey => $label): ?>
                                        <?php if (!isset($currentAssignments[$roleKey])): ?>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                        <?php $assignment = $currentAssignments[$roleKey]; ?>
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="roles[]" value="<?= htmlspecialchars($roleKey); ?>"
                                                    <?= in_array($roleKey, $selectedRoles, true) ? 'checked' : ''; ?>>
                                            </td>
                                            <td><?= htmlspecialchars($label); ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($assignment['lecturer_name']); ?></strong><br>
                                                <span class="text-muted small"><?= htmlspecialchars($assignment['lecturer_username']); ?></span>
                                            </td>
                                            <td><?= htmlspecialchars(date('d M Y', strtotime($assignment['effective_date']))); ?></td>
                                            <td>
                                                <?php if (!empty($assignment['reason'])): ?>
                                                    <span class="text-muted small"><?= nl2br(htmlspecialchars($assignment['reason'])); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">Tidak ada catatan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Alasan Pembatalan <span class="text-muted small">(opsional)</span></label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Contoh: Mahasiswa mengajukan pergantian penguji"><?= htmlspecialchars($reason); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                Setelah dibatalkan, mahasiswa tidak memiliki penetapan pada peran yang dipilih.
                            </span>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times-circle me-1"></i> Batalkan Penetapan Terpilih
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
