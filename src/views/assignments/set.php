<?php
    $students = $students ?? [];
    $lecturers = $lecturers ?? [];
    $selectedStudentId = $selectedStudentId ?? null;
    $currentAssignments = $currentAssignments ?? [];
    $errors = $errors ?? [];
    $success = $success ?? null;
    $activeAngkatan = $activeAngkatan ?? [];
    $useActiveAngkatan = $useActiveAngkatan ?? true;
?>

<div class="row">
    <div class="col-12">
        <h2>Penetapan Pembimbing & Penguji</h2>
        <p>Menetapkan pembimbing dan penguji untuk mahasiswa</p>
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

<div class="row mb-3">
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="activeAngkatanSwitch"
                   onchange="toggleActiveAngkatanFilter()" <?= $useActiveAngkatan ? 'checked' : '' ?>>
            <label class="form-check-label" for="activeAngkatanSwitch">
                Gunakan filter angkatan aktif
                <?php if (!empty($activeAngkatan)): ?>
                    (<?= implode(', ', array_map('htmlspecialchars', $activeAngkatan)) ?>)
                <?php else: ?>
                    (tidak ada angkatan aktif)
                <?php endif; ?>
            </label>
        </div>
    </div>
</div>

<script>
function toggleActiveAngkatanFilter() {
    const checkbox = document.getElementById('activeAngkatanSwitch');
    const url = new URL(window.location.href);
    url.searchParams.set('active_angkatan', checkbox.checked ? '1' : '0');
    // Remove student parameter to avoid loading a specific student when toggling filter
    url.searchParams.delete('student');
    window.location.href = url.toString();
}
</script>

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
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Penetapan</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="/assignments/set" class="mb-3">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Mahasiswa</label>
                        <select class="form-select select2-student" id="student_id" name="student" required>
                            <option value="">Pilih Mahasiswa</option>
                            <?php foreach ($students as $student): ?>
                                <?php
                                    $selected = ($selectedStudentId && (int)$selectedStudentId === (int)$student['id']) ? 'selected' : '';
                                    $labelParts = [
                                        htmlspecialchars($student['nim']),
                                        htmlspecialchars($student['name'])
                                    ];
                                    if (!empty($student['has_approved_title'])) {
                                        $labelParts[] = '[Judul disetujui]';
                                    } elseif (!empty($student['title_label'])) {
                                        $labelParts[] = '[' . htmlspecialchars($student['title_label']) . ']';
                                    } else {
                                        $labelParts[] = '[Belum ajukan judul]';
                                    }
                                    $assignmentTag = !empty($student['assignment_count']) ? '[Sudah ada penetapan]' : '[Belum ada penetapan]';
                                    $labelParts[] = $assignmentTag;
                                ?>
                                <option value="<?= htmlspecialchars($student['id']); ?>" <?= $selected; ?>>
                                    <?= implode(' - ', $labelParts); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Muat Penetapan</button>
                </form>

                <hr class="mb-4">

                <form method="POST" action="/assignments/set">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="student_id_form" class="form-label">Mahasiswa</label>
                        <select class="form-select select2-student" id="student_id_form" name="student_id" required>
                            <option value="">Pilih Mahasiswa</option>
                            <?php foreach ($students as $student): ?>
                                <?php
                                    $selected = '';
                                    if (isset($_POST['student_id']) && (int)$_POST['student_id'] === (int)$student['id']) {
                                        $selected = 'selected';
                                    } elseif ($selectedStudentId && (int)$selectedStudentId === (int)$student['id']) {
                                        $selected = 'selected';
                                    }
                                    $labelParts = [
                                        htmlspecialchars($student['nim']),
                                        htmlspecialchars($student['name'])
                                    ];
                                    if (!empty($student['has_approved_title'])) {
                                        $labelParts[] = '[Judul disetujui]';
                                    } elseif (!empty($student['title_label'])) {
                                        $labelParts[] = '[' . htmlspecialchars($student['title_label']) . ']';
                                    } else {
                                        $labelParts[] = '[Belum ajukan judul]';
                                    }
                                    $assignmentTag = !empty($student['assignment_count']) ? '[Sudah ada penetapan]' : '[Belum ada penetapan]';
                                    $labelParts[] = $assignmentTag;
                                ?>
                                <option value="<?= htmlspecialchars($student['id']); ?>" <?= $selected; ?>>
                                    <?= implode(' - ', $labelParts); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pembimbing_1" class="form-label">Pembimbing 1</label>
                        <select class="form-select select2-lecturer" id="pembimbing_1" name="pembimbing_1" required>
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <?php $selected = '';
                                    if (!empty($currentAssignments['pembimbing_1']) && (int)$currentAssignments['pembimbing_1']['lecturer_id'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    } elseif (isset($_POST['pembimbing_1']) && (int)$_POST['pembimbing_1'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?= htmlspecialchars($lecturer['id']); ?>" <?= $selected; ?>>
                                    <?= htmlspecialchars($lecturer['name']); ?> (<?= htmlspecialchars($lecturer['username']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pembimbing_2" class="form-label">Pembimbing 2</label>
                        <select class="form-select select2-lecturer" id="pembimbing_2" name="pembimbing_2">
                            <option value="">Tidak Ada</option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <?php $selected = '';
                                    if (!empty($currentAssignments['pembimbing_2']) && (int)$currentAssignments['pembimbing_2']['lecturer_id'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    } elseif (isset($_POST['pembimbing_2']) && (int)$_POST['pembimbing_2'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?= htmlspecialchars($lecturer['id']); ?>" <?= $selected; ?>>
                                    <?= htmlspecialchars($lecturer['name']); ?> (<?= htmlspecialchars($lecturer['username']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="penguji_ketua" class="form-label">Ketua Penguji</label>
                        <select class="form-select select2-lecturer" id="penguji_ketua" name="penguji_ketua" required>
                            <option value="">Pilih Dosen</option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <?php $selected = '';
                                    if (!empty($currentAssignments['penguji_1']) && (int)$currentAssignments['penguji_1']['lecturer_id'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    } elseif (isset($_POST['penguji_ketua']) && (int)$_POST['penguji_ketua'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?= htmlspecialchars($lecturer['id']); ?>" <?= $selected; ?>>
                                    <?= htmlspecialchars($lecturer['name']); ?> (<?= htmlspecialchars($lecturer['username']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="penguji_anggota_1" class="form-label">Penguji Anggota 1</label>
                        <select class="form-select select2-lecturer" id="penguji_anggota_1" name="penguji_anggota_1">
                            <option value="">Tidak Ada</option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <?php $selected = '';
                                    if (!empty($currentAssignments['penguji_2']) && (int)$currentAssignments['penguji_2']['lecturer_id'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    } elseif (isset($_POST['penguji_anggota_1']) && (int)$_POST['penguji_anggota_1'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?= htmlspecialchars($lecturer['id']); ?>" <?= $selected; ?>>
                                    <?= htmlspecialchars($lecturer['name']); ?> (<?= htmlspecialchars($lecturer['username']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="penguji_anggota_2" class="form-label">Penguji Anggota 2</label>
                        <select class="form-select select2-lecturer" id="penguji_anggota_2" name="penguji_anggota_2">
                            <option value="">Tidak Ada</option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <?php $selected = '';
                                    if (!empty($currentAssignments['penguji_3']) && (int)$currentAssignments['penguji_3']['lecturer_id'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    } elseif (isset($_POST['penguji_anggota_2']) && (int)$_POST['penguji_anggota_2'] === (int)$lecturer['id']) {
                                        $selected = 'selected';
                                    }
                                ?>
                                <option value="<?= htmlspecialchars($lecturer['id']); ?>" <?= $selected; ?>>
                                    <?= htmlspecialchars($lecturer['name']); ?> (<?= htmlspecialchars($lecturer['username']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($_POST['notes'] ?? $currentAssignments['notes'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Penetapan</button>
                    <a href="/students/assignment-status" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Petunjuk</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Pilih mahasiswa terlebih dahulu</li>
                    <li>Lengkapi form penetapan pembimbing dan penguji</li>
                    <li>Klik "Simpan Penetapan" untuk menyimpan perubahan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Select2 untuk dropdown mahasiswa
    if (typeof $ !== 'undefined' && $('.select2-student').length) {
        $('.select2-student').select2({
            placeholder: "Cari dan pilih mahasiswa...",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Mahasiswa tidak ditemukan";
                }
            }
        });
    }
    
    // Inisialisasi Select2 untuk dropdown dosen
    if (typeof $ !== 'undefined' && $('.select2-lecturer').length) {
        $('.select2-lecturer').select2({
            placeholder: "Cari dan pilih dosen...",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Dosen tidak ditemukan";
                }
            }
        });
    }
});
</script>
