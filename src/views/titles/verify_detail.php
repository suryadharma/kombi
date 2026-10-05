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

// Group lecturers by type for better organization
$advisorRoles = ['pembimbing_1', 'pembimbing_2'];
$examinerRoles = ['penguji_1', 'penguji_2', 'penguji_3'];
?>

<div class="row">
    <div class="col-12">
        <h2>Verifikasi Judul Skripsi</h2>
        <p>Detail pengajuan judul skripsi</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Student Information Card -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="text-muted small mb-1">NIM</label>
                        <div class="fw-bold"><?= htmlspecialchars($title['nim'] ?? '') ?></div>
                    </div>
                    <div class="col-md-8">
                        <label class="text-muted small mb-1">Nama Mahasiswa</label>
                        <div class="fw-bold"><?= htmlspecialchars($title['student_name'] ?? '') ?></div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <label class="text-muted small mb-1">Tanggal Pengajuan</label>
                        <div><?= date('d M Y H:i', strtotime($title['submitted_at'])) ?></div>
                    </div>
                    <div class="col-md-8">
                        <label class="text-muted small mb-1">Judul Skripsi</label>
                        <div class="fw-bold"><?= htmlspecialchars($title['title'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lecturers Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Dosen Pembimbing & Penguji</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;">Peran</th>
                            <th style="width: 40%;">Nama Dosen</th>
                            <th style="width: 25%;">Surat Tugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $hasLecturers = false;
                        
                        // Display Advisor Roles
                        foreach ($advisorRoles as $roleKey):
                            $lecturer = $proposedLecturers[$roleKey] ?? null;
                            $letterLink = $letterLinks['advisor'][$roleKey] ?? null;
                            if ($lecturer && !empty($lecturer['name'])):
                                $hasLecturers = true;
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($lecturer['label']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold"><?= htmlspecialchars($lecturer['name']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($letterLink)): ?>
                                    <a href="<?= htmlspecialchars($letterLink) ?>" 
                                       target="_blank" 
                                       rel="noopener" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Lihat Surat Tugas">
                                        <i class="fas fa-file-pdf me-1"></i>Lihat Surat
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Tidak tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        
                        // Display Examiner Roles
                        foreach ($examinerRoles as $roleKey):
                            $lecturer = $proposedLecturers[$roleKey] ?? null;
                            $letterLink = $letterLinks['examiner'][$roleKey] ?? null;
                            if ($lecturer && !empty($lecturer['name'])):
                                $hasLecturers = true;
                        ?>
                        <tr>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-user-check me-1"></i><?= htmlspecialchars($lecturer['label']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold"><?= htmlspecialchars($lecturer['name']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($letterLink)): ?>
                                    <a href="<?= htmlspecialchars($letterLink) ?>" 
                                       target="_blank" 
                                       rel="noopener" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Lihat Surat Tugas">
                                        <i class="fas fa-file-pdf me-1"></i>Lihat Surat
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Tidak tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        
                        if (!$hasLecturers):
                        ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                <i class="fas fa-inbox me-2"></i>Belum ada dosen yang ditetapkan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 1rem;">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Status Verifikasi</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/titles/verify/<?= $title['id'] ?>/process">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="DITERIMA">✅ Diterima</option>
                            <option value="DITOLAK">❌ Ditolak</option>
                            <option value="PERLU_REVISI">📝 Perlu Revisi</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Catatan</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4" 
                                  placeholder="Tambahkan catatan verifikasi..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Verifikasi
                        </button>
                        <a href="/titles/verify" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
