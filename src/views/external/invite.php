<?php $studentsList = $students ?? []; $stageOptions = $stageOptions ?? []; ?>

<div class="row">
    <div class="col-12">
        <h2>Undangan Penguji Eksternal</h2>
        <p>Buat token atau akun sementara untuk penguji eksternal</p>
    </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Undangan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/external/invite">
                    <?php echo Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="examiner_name" class="form-label">Nama Penguji Eksternal</label>
                        <input type="text" class="form-control" id="examiner_name" name="examiner_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="examiner_nip" class="form-label">NIP/NIDN</label>
                        <input type="text" class="form-control" id="examiner_nip" name="examiner_nip" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Mahasiswa yang Diuji</label>
                        <select class="form-select select2-autocomplete" id="student_id" name="student_id" required style="width: 100%;">
                            <option value="">Pilih Mahasiswa</option>
                            <?php if (!empty($studentsList)): ?>
                                <?php foreach ($studentsList as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['nim'] . ' - ' . $student['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Ketik untuk mencari mahasiswa berdasarkan NIM atau nama</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stage" class="form-label">Tahap Ujian</label>
                        <select class="form-select" id="stage" name="stage" required>
                            <option value="">Pilih Tahap</option>
                            <?php foreach ($stageOptions as $key => $label): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="event_date" class="form-label">Tanggal Ujian</label>
                        <input type="date" class="form-control" id="event_date" name="event_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="event_time" class="form-label">Waktu Ujian</label>
                        <input type="time" class="form-control" id="event_time" name="event_time" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="room" class="form-label">Ruangan</label>
                        <input type="text" class="form-control" id="room" name="room" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Metode Akses</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="access_method" id="access_token" value="token" checked>
                                <label class="form-check-label" for="access_token">Token Sementara</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="access_method" id="access_account" value="account">
                                <label class="form-check-label" for="access_account">Akun Sementara</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Buat Undangan</button>
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
                    <li>Isi data penguji eksternal dengan lengkap</li>
                    <li>Pilih metode akses: token sementara atau akun sementara</li>
                    <li>Token akan kedaluwarsa otomatis setelah ujian</li>
                    <li>Akun sementara akan dinonaktifkan otomatis H+3</li>
                </ul>
                
                <div class="alert alert-info">
                    <strong>Info:</strong> Penguji eksternal akan menerima email dengan instruksi akses.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 with autocomplete for student selection
    $('.select2-autocomplete').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Ketik NIM atau nama mahasiswa...',
        allowClear: true
    });

    // Store original text of stage options on page load
    $('#stage option').each(function () {
        if ($(this).val() && !$(this).data('original-text')) {
            $(this).data('original-text', $(this).text());
        }
    });

    // Update stage dropdown progress when a student is selected
    $('#student_id').on('select2:select change', function () {
        const studentId = $(this).val();

        // Reset all options first
        $('#stage option').prop('disabled', false).each(function () {
            if ($(this).val()) {
                $(this).text($(this).data('original-text') || $(this).text());
            }
        });

        if (!studentId) {
            return;
        }

        fetch(`/events/check-progress?student_id=${studentId}`, {
            headers: { 'Accept': 'application/json' }
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

                // Reset selection if the currently selected stage is now disabled
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
    });
});
</script>
