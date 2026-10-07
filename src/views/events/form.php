<?php
$mode = $mode ?? 'create';
$event = $event ?? [];
$stageOptions = $stageOptions ?? [];
$statusOptions = $statusOptions ?? [];
$students = $students ?? [];
$externalTokens = $externalTokens ?? [];
$externalLecturers = $externalLecturers ?? [];
$error = $error ?? null;
$success = $success ?? null;

$isEdit = $mode === 'edit';
$formAction = $isEdit ? "/events/" . htmlspecialchars($event['id']) . "/update" : "/events/store";
$pageTitle = $isEdit ? 'Ubah Penjadwalan' : 'Tambah Penjadwalan';
$submitLabel = $isEdit ? 'Perbarui Jadwal' : 'Simpan Jadwal';
?>

<div class="row mb-3 align-items-center">
    <div class="col-md-8">
        <h2 class="mb-1"><i class="fas fa-calendar-plus"></i> <?= htmlspecialchars($pageTitle) ?></h2>
        <p class="text-muted mb-0">
            Pastikan jadwal disesuaikan dengan kesiapan mahasiswa dan penguji. Status otomatis di-set ke
            <strong>MENUNGGU</strong> saat jadwal dibuat.
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="/events" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ !== 'undefined' && $('.select2-student').length) {
            $('.select2-student').select2({
                placeholder: "Cari dan pilih mahasiswa yang siap dijadwalkan...",
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function () {
                        return "Mahasiswa tidak memenuhi syarat atau tidak ditemukan";
                    }
                }
            });
        }

        const externalButtons = document.querySelectorAll('.select-external-lecturer');
        if (externalButtons.length) {
            const externalNameField = document.getElementById('external_name');
            const externalNipField = document.getElementById('external_nip');
            externalButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (externalNameField) {
                        externalNameField.value = this.dataset.name || '';
                    }
                    if (externalNipField) {
                        externalNipField.value = this.dataset.nip || '';
                    }
                    const modalElement = document.getElementById('externalLecturerModal');
                    if (modalElement) {
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }
                });
            });
        }
        // Store original text on page load
        $('#stage option').each(function () {
            if ($(this).val() && !$(this).data('original-text')) {
                $(this).data('original-text', $(this).text());
            }
        });

        // Use Select2-specific event for more reliable triggering
        $('.select2-student').on('select2:select change', function (e) {
            const studentId = $(this).val();

            if (!studentId) {
                // Hide lecturer info
                $('#lecturerAssignmentsInfo').hide();
                $('#scheduleConflictWarning').hide();
                
                // Reset all options if no student selected
                $('#stage option').prop('disabled', false).each(function () {
                    if ($(this).val()) {
                        $(this).text($(this).data('original-text') || $(this).text());
                    }
                });
                return;
            }

            // Show lecturer loading
            $('#lecturerAssignmentsInfo').show();
            $('#lecturerLoading').show();
            $('#lecturerContent').hide();

            // Fetch lecturer assignments
            fetch(`/events/get-lecturer-assignments?student_id=${studentId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 401) {
                        // Session expired, redirect to login
                        window.location.href = '/login';
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then(data => {
                    $('#lecturerLoading').hide();
                    
                    if (data.success && data.lecturers && data.lecturers.length > 0) {
                        let html = '<div class="row g-2">';
                        data.lecturers.forEach(function(lecturer) {
                            html += `
                                <div class="col-md-6">
                                    <div class="p-2 bg-white rounded border">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-primary">${lecturer.role_label}</div>
                                                <div class="small">${lecturer.lecturer_name}</div>
                                                ${lecturer.lecturer_nip ? `<div class="small text-muted">NIP: ${lecturer.lecturer_nip}</div>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        $('#lecturerContent').html(html).show();
                    } else {
                        $('#lecturerContent').html('<div class="text-muted small">Belum ada dosen yang ditetapkan.</div>').show();
                    }
                })
                .catch(error => {
                    if (error.message !== 'Session expired') {
                        $('#lecturerLoading').hide();
                        $('#lecturerContent').html('<div class="text-danger small">Gagal memuat informasi dosen.</div>').show();
                        console.error('Error fetching lecturer assignments:', error);
                    }
                });

            // Reset all options first
            $('#stage option').prop('disabled', false).each(function () {
                if ($(this).val()) {
                    $(this).text($(this).data('original-text') || $(this).text());
                }
            });

            // Fetch and update progress
            fetch(`/events/check-progress?student_id=${studentId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then(data => {
                    const stages = data.stages || {};
                    Object.keys(stages).forEach(stage => {
                        const info = stages[stage];
                        const option = $(`#stage option[value="${stage}"]`);
                        if (!option.length) {
                            return;
                        }
                        const originalText = option.data('original-text') || option.text();

                        if (info.completed) {
                            option.prop('disabled', true);
                            option.text(`${originalText} — selesai dinilai`);
                        } else if (info.has_event && info.event_status === 'MENUNGGU') {
                            const assigned = info.assigned || 0;
                            const submitted = info.submitted || 0;
                            if (submitted === 0) {
                                option.text(`${originalText} — sudah dijadwalkan, dosen belum input nilai`);
                            } else {
                                option.text(`${originalText} — sudah dijadwalkan, ${submitted}/${assigned} dosen sudah menilai`);
                            }
                        } else if (info.has_event && info.event_status === 'BATAL') {
                            option.text(`${originalText} — pernah dibatalkan`);
                        }
                    });

                    // If currently selected option is now disabled, reset selection
                    const selectedOption = $('#stage option:selected');
                    if (selectedOption.prop('disabled')) {
                        $('#stage').val('');
                    }
                })
                .catch(error => {
                    if (error.message !== 'Session expired') {
                        console.error('Error fetching student progress:', error);
                    }
                });

            // Check for schedule conflicts
            checkScheduleConflicts();
        });

        // Check schedule conflicts when date, time, or room changes
        $('#scheduled_date, #scheduled_time, #room').on('change', function() {
            checkScheduleConflicts();
        });

        function checkScheduleConflicts() {
            const studentId = $('#student_id').val();
            const scheduledDate = $('#scheduled_date').val();
            const scheduledTime = $('#scheduled_time').val();
            const room = $('#room').val();
            const eventId = <?= $isEdit ? (int)($event['id'] ?? 0) : '0' ?>;

            if (!studentId || !scheduledDate || !scheduledTime) {
                $('#scheduleConflictWarning').hide();
                return;
            }

            // Show loading
            $('#scheduleConflictWarning').hide();

            fetch(`/events/check-schedule-conflict?student_id=${studentId}&scheduled_date=${scheduledDate}&scheduled_time=${scheduledTime}&room=${encodeURIComponent(room)}&exclude_event_id=${eventId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        throw new Error('Session expired');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (data.has_conflicts && data.conflicts) {
                            let html = '<p class="mb-2">Konflik Jadwal Ditemukan!</p><ul class="mb-0">';
                            
                            for (const [key, conflicts] of Object.entries(data.conflicts)) {
                                if (key === '[RUANGAN]') {
                                    html += `<li><strong>Konflik Ruangan:</strong><ul class="mt-1">`;
                                } else {
                                    html += `<li><strong>${key}:</strong><ul class="mt-1">`;
                                }
                                conflicts.forEach(function(conflict) {
                                    html += `<li>${conflict.student} - ${conflict.event_type} (${conflict.time}) di ${conflict.room}</li>`;
                                });
                                html += '</ul></li>';
                            }
                            
                            html += '</ul><p class="mb-0 mt-2"><small class="text-muted">Pertimbangkan untuk mengubah jadwal atau memindahkan ke waktu lain.</small></p>';
                            
                            $('#conflictDetails').html(html);
                            $('#scheduleConflictWarning').show();
                        } else {
                            $('#scheduleConflictWarning').hide();
                        }
                    }
                })
                .catch(error => {
                    if (error.message !== 'Session expired') {
                        console.error('Error checking schedule conflicts:', error);
                        $('#scheduleConflictWarning').hide();
                    }
                });
        }
    });</script>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Form Penjadwalan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $formAction ?>">
                    <?= Csrf::field(); ?>

                    <div class="mb-3">
                        <label for="student_id" class="form-label">Mahasiswa *</label>
                        <select class="form-select select2-student" id="student_id" name="student_id" required>
                            <option value="">Pilih Mahasiswa</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']) ?>" <?= ((int) ($event['student_id'] ?? 0) === (int) $student['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['nim'] . ' - ' . $student['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Lecturer Assignments Info -->
                    <div id="lecturerAssignmentsInfo" class="mb-3" style="display: none;">
                        <label class="form-label">Dosen Pembimbing & Penguji</label>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div id="lecturerLoading" class="text-center py-2">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <small class="text-muted ms-2">Memuat informasi dosen...</small>
                                </div>
                                <div id="lecturerContent" style="display: none;">
                                    <!-- Lecturer info will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stage" class="form-label">Tahap Ujian *</label>
                        <select class="form-select" id="stage" name="stage" required>
                            <option value="">Pilih Tahap</option>
                            <?php foreach ($stageOptions as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= ($event['stage'] ?? '') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="scheduled_date" class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date"
                                value="<?= htmlspecialchars($event['scheduled_date'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="scheduled_time" class="form-label">Waktu *</label>
                            <input type="time" class="form-control" id="scheduled_time" name="scheduled_time"
                                value="<?= htmlspecialchars(substr($event['scheduled_time'] ?? '09:00', 0, 5)) ?>"
                                required>
                            <div class="form-text">Gunakan format 24 jam (WIB).</div>
                        </div>
                    </div>

                    <!-- Schedule Conflict Warning -->
                    <div id="scheduleConflictWarning" class="alert alert-warning" style="display: none;">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle fa-lg me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading mb-2">Konflik Jadwal Ditemukan!</h6>
                                <div id="conflictDetails"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="room" class="form-label">Ruang Pelaksanaan *</label>
                        <input type="text" class="form-control" id="room" name="room" maxlength="50"
                            value="<?= htmlspecialchars($event['room'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= ($event['status'] ?? 'MENUNGGU') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <hr>
                    <h6 class="fw-semibold">Penguji Eksternal (Opsional)</h6>
                    <p class="text-muted small">Isi data di bawah untuk menghasilkan token penguji eksternal ketika
                        jadwal disimpan.</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="external_name" class="form-label">Nama Penguji Eksternal</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="external_name" name="external_name"
                                    value="<?= htmlspecialchars($event['external_name'] ?? '') ?>">
                                <?php if (!empty($externalLecturers)): ?>
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#externalLecturerModal">
                                        <i class="fas fa-address-book"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Klik ikon buku untuk memilih dari daftar dosen eksternal yang
                                tersimpan.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="external_nip" class="form-label">NIP Penguji Eksternal</label>
                            <input type="text" class="form-control" id="external_nip" name="external_nip"
                                value="<?= htmlspecialchars($event['external_nip'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> <?= htmlspecialchars($submitLabel) ?>
                        </button>
                        <a href="/events" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-circle-info me-2"></i> Panduan</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><strong>Tahap</strong> menentukan jenis evento (Sempro/Semhas/Ujian).</li>
                    <li class="mb-2">Satu mahasiswa hanya boleh memiliki <strong>satu jadwal</strong> per tahap.</li>
                    <li class="mb-2">Status dapat diubah ke <em>Selesai</em> atau <em>Batal</em> setelah kegiatan.</li>
                    <li>Jika data penguji eksternal diisi, sistem otomatis membuat token akses untuk mereka.</li>
                </ul>
            </div>
        </div>

        <?php if (!empty($externalLecturers)): ?>
            <div class="modal fade" id="externalLecturerModal" tabindex="-1" aria-labelledby="externalLecturerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="externalLecturerModalLabel"><i
                                    class="fas fa-address-book me-2"></i>Pilih Dosen Eksternal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama</th>
                                            <th>NIP</th>
                                            <th class="text-center" style="width: 120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($externalLecturers as $lecturer): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($lecturer['name']) ?></td>
                                                <td><?= htmlspecialchars($lecturer['nip'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-primary select-external-lecturer"
                                                        data-name="<?= htmlspecialchars($lecturer['name']) ?>"
                                                        data-nip="<?= htmlspecialchars($lecturer['nip'] ?? '') ?>">
                                                        <i class="fas fa-check"></i> Pilih
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isEdit && !empty($externalTokens)): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i> Token Penguji Eksternal</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Riwayat token yang pernah dibuat untuk jadwal ini.</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Token</th>
                                    <th>Berlaku s.d</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($externalTokens as $token): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($token['token']) ?></code></td>
                                        <td><?= htmlspecialchars(date('d M Y H:i', strtotime($token['expires_at']))) ?></td>
                                        <td>
                                            <?php if (!empty($token['used_at'])): ?>
                                                <span class="badge bg-success">Dipakai</span>
                                            <?php elseif (strtotime($token['expires_at']) < time()): ?>
                                                <span class="badge bg-secondary">Kedaluwarsa</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>