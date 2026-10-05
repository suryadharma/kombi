<?php
$student = $student ?? null;
$lecturers = $lecturers ?? [];
$currentAssignments = $currentAssignments ?? [];
$roleLabels = $roleLabels ?? [];
$errors = $errors ?? [];
$success = $success ?? null;

// Build current assignments map
$assignmentMap = [];
foreach ($currentAssignments as $assignment) {
    $assignmentMap[$assignment['role']] = $assignment;
}
?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Ganti Dosen - <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['nim']) ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
<div class="mb-4">
    <h6>Dosen Saat Ini:</h6>
    <table class="table table-sm">
        <?php foreach ($roleLabels as $roleKey => $roleLabel): ?>
            <tr>
                <th><?= htmlspecialchars($roleLabel) ?></th>
                <td>
                    <?php if (isset($assignmentMap[$roleKey])): ?>
                        <?= htmlspecialchars($assignmentMap[$roleKey]['lecturer_name']) ?>
                    <?php else: ?>
                        <em class="text-muted">Belum ditentukan</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<form method="POST" action="/assignments/change/<?= $student['id'] ?>">
    <?= Csrf::field(); ?>
    <div class="mb-3">
        <label for="role" class="form-label">Pilih Role yang Akan Diganti</label>
        <select class="form-select" id="role" name="role" required>
            <option value="">-- Pilih Role --</option>
            <?php $hasOptions = false; ?>
            <?php foreach ($roleLabels as $roleKey => $roleLabel): ?>
                <?php if (!isset($assignmentMap[$roleKey])) { continue; } ?>
                <?php $hasOptions = true; ?>
                <option value="<?= htmlspecialchars($roleKey) ?>"
                        data-current-lecturer="<?= htmlspecialchars($assignmentMap[$roleKey]['lecturer_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($roleLabel) ?> (Saat ini: <?= htmlspecialchars($assignmentMap[$roleKey]['lecturer_name']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!$hasOptions): ?>
            <div class="form-text text-warning">Mahasiswa ini belum memiliki penetapan aktif untuk diganti.</div>
        <?php endif; ?>
    </div>
            
            <div class="mb-3">
                <label for="current_lecturer" class="form-label">Dosen Saat Ini</label>
                <input type="text" class="form-control" id="current_lecturer" readonly>
            </div>
            
            <div class="mb-3">
                <label for="new_lecturer_id" class="form-label">Dosen Baru</label>
                <select class="form-select" id="new_lecturer_id" name="new_lecturer_id" required>
                    <option value="">-- Pilih Dosen --</option>
                    <?php foreach ($lecturers as $lecturer): ?>
                        <option value="<?= $lecturer['id'] ?>"><?= htmlspecialchars($lecturer['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="effective_date" class="form-label">Tanggal Efektif</label>
                <input type="date" class="form-control" id="effective_date" name="effective_date" value="<?= date('Y-m-d') ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="reason" class="form-label">Alasan Pergantian</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Tuliskan alasan pergantian..."></textarea>
            </div>
            
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="/students/<?= $student['id'] ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const currentLecturerInput = document.getElementById('current_lecturer');

    function updateCurrentLecturer() {
        if (!roleSelect || !currentLecturerInput) {
            return;
        }
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const value = selectedOption ? (selectedOption.getAttribute('data-current-lecturer') || '') : '';
        currentLecturerInput.value = value;
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updateCurrentLecturer);
    }

    updateCurrentLecturer();
});
</script>
