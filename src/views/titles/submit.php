<?php
$formData = $formData ?? [];
$studentsList = $students ?? [];
$studentAssignments = $studentAssignments ?? [];
$lecturerOptions = $lecturerOptions ?? [];
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
$success = $success ?? null;
$showAdminEditLink = $showAdminEditLink ?? false;
$allowLetterLinkUpdate = $allowLetterLinkUpdate ?? false;
$letterUpdateLecturers = $letterUpdateLecturers ?? [];

// Group roles by type
$advisorRoles = ['pembimbing_1', 'pembimbing_2'];
$examinerRoles = ['penguji_1', 'penguji_2', 'penguji_3'];

// Helper function for required label
function renderRequiredLabel($isRequired) {
    if ($isRequired) {
        return '<span class="text-danger">*</span>';
    }
    return '<span class="text-muted">(Opsional)</span>';
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-1">Pengajuan Judul Skripsi</h2>
        <p class="text-muted">Lengkapi formulir di bawah ini untuk mengajukan judul skripsi Anda</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle fa-lg me-3"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle fa-lg me-3"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($existingTitle)): ?>
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php if ($showAdminEditLink): ?>
                            Mahasiswa ini sudah memiliki judul
                        <?php else: ?>
                            Anda sudah memiliki pengajuan judul
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold">Judul Skripsi</label>
                        <div class="p-3 bg-light rounded border">
                            <?php echo nl2br(htmlspecialchars($existingTitle['title'])); ?>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Status</label>
                            <div class="mt-1">
                                <?php
                                $statusClass = 'bg-secondary';
                                $statusIcon = 'fa-clock';
                                switch ($existingTitle['status']) {
                                    case 'MENUNGGU':
                                        $statusClass = 'bg-warning text-dark';
                                        $statusIcon = 'fa-hourglass-half';
                                        break;
                                    case 'DITERIMA':
                                        $statusClass = 'bg-success';
                                        $statusIcon = 'fa-check-circle';
                                        break;
                                    case 'DITOLAK':
                                        $statusClass = 'bg-danger';
                                        $statusIcon = 'fa-times-circle';
                                        break;
                                    case 'PERLU_REVISI':
                                        $statusClass = 'bg-info text-dark';
                                        $statusIcon = 'fa-edit';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?> fs-6">
                                    <i class="fas <?php echo $statusIcon; ?> me-1"></i><?php echo htmlspecialchars($existingTitle['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Tanggal Pengajuan</label>
                            <div class="mt-1">
                                <i class="far fa-calendar me-1"></i><?php echo date('d M Y', strtotime($existingTitle['submitted_at'])); ?>
                                <br>
                                <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo date('H:i', strtotime($existingTitle['submitted_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($existingTitle['verified_by_name'])): ?>
                    <div class="mb-3">
                        <label class="text-muted small fw-bold">Diverifikasi Oleh</label>
                        <div class="mt-1">
                            <i class="fas fa-user-check me-1"></i><?php echo htmlspecialchars($existingTitle['verified_by_name']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($showAdminEditLink): ?>
                        <a href="/titles/<?php echo htmlspecialchars($existingTitle['id']); ?>/edit" class="btn btn-primary">
                            <i class="fas fa-pen me-1"></i> Edit Judul
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($hasTitle) && $hasTitle): ?>
            <div class="alert alert-info d-flex align-items-start">
                <i class="fas fa-info-circle fa-lg me-3 mt-1"></i>
                <div>
                    <?php if ($allowLetterLinkUpdate): ?>
                        <strong>Perbarui Link Surat Tugas</strong><br>
                        Anda tetap dapat memperbarui link surat tugas untuk dosen yang sama melalui formulir di bawah ini.
                    <?php else: ?>
                        <strong>Pengajuan Judul Sudah Ada</strong><br>
                        Anda sudah memiliki pengajuan judul. Hubungi Kombi jika ingin melakukan perubahan.
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($allowLetterLinkUpdate && !empty($letterUpdateLecturers)): ?>
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Perbarui Link Surat Tugas</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Perbarui tautan Drive/OneDrive jika ada revisi surat tugas pembimbing atau penguji.</p>
                    <form method="POST" action="/titles/submit">
                        <?php echo Csrf::field(); ?>
                        <input type="hidden" name="form_mode" value="update_letter_links">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Peran</th>
                                        <th style="width: 35%;">Dosen</th>
                                        <th style="width: 35%;">Link Surat Tugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roleMeta as $roleKey => $meta): ?>
                                        <?php
                                            $info = $letterUpdateLecturers[$roleKey] ?? [];
                                            $label = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
                                            $fieldName = 'letter_link_' . $roleKey;
                                            $roleLinkValue = $letterLinks[$roleKey] ?? '';
                                            $hasLecturer = !empty($info['id']);
                                            $inputRequired = !empty($meta['required']) || $hasLecturer;
                                            $isAdvisor = in_array($roleKey, $advisorRoles);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php echo $isAdvisor ? 'bg-info text-dark' : 'bg-warning text-dark'; ?>">
                                                    <?php echo htmlspecialchars($label); ?>
                                                </span>
                                                <?php echo $inputRequired ? '<span class="text-danger">*</span>' : ''; ?>
                                            </td>
                                            <td>
                                                <?php if ($hasLecturer): ?>
                                                    <i class="fas fa-user-tie me-1"></i><?php echo htmlspecialchars($info['name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum ditetapkan</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($roleLinkValue)): ?>
                                                    <div class="mb-2">
                                                        <a href="<?php echo htmlspecialchars($roleLinkValue); ?>" target="_blank" rel="noopener" class="text-decoration-none">
                                                            <i class="fas fa-external-link-alt me-1"></i>
                                                            <small><?php echo htmlspecialchars(substr($roleLinkValue, 0, 30)); ?>...</small>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <input type="url"
                                                       class="form-control form-control-sm"
                                                       id="<?php echo $fieldName; ?>"
                                                       name="<?php echo $fieldName; ?>"
                                                       placeholder="https://drive.google.com/..."
                                                       value="<?php echo htmlspecialchars($roleLinkValue); ?>"
                                                       <?php echo $inputRequired ? 'required' : ''; ?>
                                                       <?php echo $hasLecturer ? '' : 'readonly'; ?>>
                                                <?php if (!$hasLecturer): ?>
                                                    <small class="text-muted">Menunggu penetapan dosen</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted"><span class="text-danger">*</span> Wajib diisi</small>
                        
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Link Surat Tugas
                            </button>
                            <a href="/dashboard" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        <?php elseif (!empty($existingTitle) && !empty($studentsList)): ?>
            <!-- Kombi sees existing title details above; form disembunyikan -->
        <?php else: ?>
        
        <!-- Main Submission Form -->
        <form method="POST" action="/titles/submit" enctype="multipart/form-data">
            <?php echo Csrf::field(); ?>

            <!-- Student Selection (for admin) -->
            <?php if (!empty($studentsList)): ?>
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>1. Pilih Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label fw-bold">Mahasiswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php foreach ($studentsList as $studentOption): ?>
                                <option value="<?php echo $studentOption['id']; ?>"
                                    <?php echo (isset($formData['student_id']) && (int)$formData['student_id'] === (int)$studentOption['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($studentOption['nim'] . ' - ' . $studentOption['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Title Information -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>2. Informasi Judul Skripsi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Judul Skripsi Lengkap <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               required 
                               placeholder="Contoh: Implementasi Algoritma Machine Learning untuk..."
                               value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>">
                        <div class="form-text">Tulis judul skripsi dengan jelas dan lengkap sesuai format penulisan ilmiah.</div>
                    </div>
                    
                    <?php if (!empty($studentAssignments)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Dosen Pembimbing & Penguji (Penetapan Kombi):</strong>
                        <ul class="list-unstyled mb-0 mt-2">
                            <?php foreach ($studentAssignments as $role => $assignment): ?>
                                <li>
                                    <span class="badge <?php echo in_array($role, $advisorRoles) ? 'bg-info text-dark' : 'bg-warning text-dark'; ?> me-2">
                                        <?php echo htmlspecialchars($roleLabels[$role] ?? ucfirst($role)); ?>
                                    </span>
                                    <?php echo htmlspecialchars($assignment['name'] ?? '-'); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="form-text mt-2">Anda masih dapat mengusulkan dosen sesuai penetapan kampus melalui form di bawah.</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lecturer Selection -->
            <?php if (!empty($lecturerOptions)): ?>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>3. Usulan Dosen & Surat Tugas</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Pilih dosen pembimbing dan penguji sesuai SK kampus. Sertakan link surat tugas untuk setiap dosen yang dipilih.
                        </p>
                        
                        <!-- Advisor Section -->
                        <div class="mb-4">
                            <h6 class="text-primary fw-bold mb-3">
                                <i class="fas fa-user-tie me-2"></i>Dosen Pembimbing
                            </h6>
                            <?php foreach ($advisorRoles as $roleKey): ?>
                                <?php
                                    $meta = $roleMeta[$roleKey] ?? [];
                                    $roleLabel = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
                                    $selectedValue = $formData[$roleKey . '_id'] ?? ($studentAssignments[$roleKey]['id'] ?? '');
                                    $isRequired = !empty($meta['required']);
                                    $letterField = 'letter_link_' . $roleKey;
                                    $linkValue = $letterLinks[$roleKey] ?? '';
                                ?>
                                <div class="card mb-3 border-info">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="<?php echo $roleKey; ?>_id" class="form-label fw-bold">
                                                    <?php echo htmlspecialchars($roleLabel); ?>
                                                    <?php echo $isRequired ? '<span class="text-danger">*</span>' : '<span class="text-muted">(Opsional)</span>'; ?>
                                                </label>
                                                <select class="form-select"
                                                        id="<?php echo $roleKey; ?>_id"
                                                        name="<?php echo $roleKey; ?>_id"
                                                        <?php echo $isRequired ? 'required' : ''; ?>>
                                                    <option value="">-- Pilih Dosen --</option>
                                                    <?php foreach ($lecturerOptions as $lecturer): ?>
                                                        <option value="<?php echo $lecturer['id']; ?>"
                                                            <?php echo ((string)$selectedValue === (string)$lecturer['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($lecturer['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="<?php echo $letterField; ?>" class="form-label fw-bold">
                                                    Link Surat Tugas
                                                    <?php echo $isRequired ? '<span class="text-danger">*</span>' : ''; ?>
                                                </label>
                                                <input type="url"
                                                       class="form-control"
                                                       id="<?php echo $letterField; ?>"
                                                       name="<?php echo $letterField; ?>"
                                                       placeholder="https://drive.google.com/..."
                                                       <?php echo $isRequired ? 'required' : ''; ?>
                                                       value="<?php echo htmlspecialchars($linkValue); ?>">
                                                <div class="form-text">
                                                    <i class="fas fa-link me-1"></i>Google Drive / OneDrive
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Examiner Section -->
                        <div class="mb-3">
                            <h6 class="text-warning fw-bold mb-3">
                                <i class="fas fa-user-check me-2"></i>Dosen Penguji
                            </h6>
                            <?php foreach ($examinerRoles as $roleKey): ?>
                                <?php
                                    $meta = $roleMeta[$roleKey] ?? [];
                                    $roleLabel = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
                                    $selectedValue = $formData[$roleKey . '_id'] ?? ($studentAssignments[$roleKey]['id'] ?? '');
                                    $isRequired = !empty($meta['required']);
                                    $letterField = 'letter_link_' . $roleKey;
                                    $linkValue = $letterLinks[$roleKey] ?? '';
                                ?>
                                <div class="card mb-3 border-warning">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="<?php echo $roleKey; ?>_id" class="form-label fw-bold">
                                                    <?php echo htmlspecialchars($roleLabel); ?>
                                                    <?php echo $isRequired ? '<span class="text-danger">*</span>' : '<span class="text-muted">(Opsional)</span>'; ?>
                                                </label>
                                                <select class="form-select"
                                                        id="<?php echo $roleKey; ?>_id"
                                                        name="<?php echo $roleKey; ?>_id"
                                                        <?php echo $isRequired ? 'required' : ''; ?>>
                                                    <option value="">-- Pilih Dosen --</option>
                                                    <?php foreach ($lecturerOptions as $lecturer): ?>
                                                        <option value="<?php echo $lecturer['id']; ?>"
                                                            <?php echo ((string)$selectedValue === (string)$lecturer['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($lecturer['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="<?php echo $letterField; ?>" class="form-label fw-bold">
                                                    Link Surat Tugas
                                                    <?php echo $isRequired ? '<span class="text-danger">*</span>' : ''; ?>
                                                </label>
                                                <input type="url"
                                                       class="form-control"
                                                       id="<?php echo $letterField; ?>"
                                                       name="<?php echo $letterField; ?>"
                                                       placeholder="https://drive.google.com/..."
                                                       <?php echo $isRequired ? 'required' : ''; ?>
                                                       value="<?php echo htmlspecialchars($linkValue); ?>">
                                                <div class="form-text">
                                                    <i class="fas fa-link me-1"></i>Google Drive / OneDrive
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Pastikan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Link surat tugas dapat diakses publik (tanpa login)</li>
                                <li>Kolom opsional menjadi wajib diisi bila dosennya dipilih</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Data dosen tidak tersedia.</strong> Hubungi Kombi untuk mengaktifkan daftar dosen.
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="/dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Ajukan Judul
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
    
    <!-- Sidebar Help -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 1rem;">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Panduan Pengisian</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="fw-bold"><i class="fas fa-1 me-2 text-primary"></i>Judul Skripsi</h6>
                    <p class="small text-muted mb-0">Tulis judul dengan jelas sesuai format penulisan ilmiah. Hindari singkatan yang tidak umum.</p>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6 class="fw-bold"><i class="fas fa-2 me-2 text-primary"></i>Pilih Dosen</h6>
                    <p class="small text-muted mb-0">Pilih dosen pembimbing dan penguji sesuai SK penetapan kampus. Pastikan dosen yang dipilih bersedia.</p>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6 class="fw-bold"><i class="fas fa-3 me-2 text-primary"></i>Link Surat Tugas</h6>
                    <p class="small text-muted mb-0">
                        Unggah surat tugas ke Google Drive/OneDrive dan pastikan:
                    </p>
                    <ul class="small text-muted mb-0">
                        <li>Link dapat diakses tanpa login (Anyone with the link)</li>
                        <li>File berisi SK/surat tugas resmi</li>
                        <li>Nama dosen tertera jelas dalam surat</li>
                    </ul>
                </div>
                
                <hr>
                
                <div class="mb-0">
                    <h6 class="fw-bold"><i class="fas fa-4 me-2 text-primary"></i>Verifikasi</h6>
                    <p class="small text-muted mb-0">Kombi akan memverifikasi pengajuan Anda. Status dapat dilihat di dashboard.</p>
                </div>
            </div>
        </div>
    </div>
</div>
