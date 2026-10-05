<?php
$student = $student ?? null;
$titleStatus = $titleStatus ?? null;
$stageStatus = $stageStatus ?? [];
$nextAction = $nextAction ?? 'Ajukan judul skripsi';
$nextEvent = $nextEvent ?? null;
$eventLabels = [
    'SEMPRO' => 'Seminar Proposal',
    'SEMHAS' => 'Seminar Hasil',
    'PRA_UJIAN' => 'Pra-Ujian',
    'UJIAN_SKRIPSI' => 'Ujian Skripsi'
];
?>

<style>
.stage-timeline {
    position: relative;
    padding-left: 1.5rem;
    border-left: 3px solid #dee2e6;
}
.stage-timeline .stage-item {
    position: relative;
    padding-bottom: 1.2rem;
    padding-left: 0.75rem;
}
.stage-timeline .stage-item:last-child {
    padding-bottom: 0;
}
.stage-timeline .stage-item::before {
    content: '';
    position: absolute;
    left: -0.8rem;
    top: 0.2rem;
    width: 0.9rem;
    height: 0.9rem;
    border-radius: 50%;
    background-color: #0d6efd;
}
.stage-timeline .stage-item.completed::before {
    background-color: #198754;
}
.stage-timeline .stage-item.pending::before {
    background-color: #ffc107;
}
.stage-timeline .stage-item.todo::before {
    background-color: #adb5bd;
}
</style>

<div class="row align-items-center mb-4">
    <div class="col-12">
        <h2 class="mb-1"><i class="fas fa-user-graduate"></i> Dashboard Mahasiswa</h2>
        <p class="text-muted mb-0">Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?>. Pantau progres skripsi Anda di sini.</p>
    </div>
</div>

<?php if ($student): ?>
<div class="row g-3 mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Status Mahasiswa</h6>
                <h3 class="fw-bold mb-1">Angkatan <?= htmlspecialchars($student['angkatan']) ?></h3>
                <span class="badge bg-info">Status: <?= htmlspecialchars($student['status']) ?></span>
                <p class="text-muted small mt-2 mb-0">Semester Masuk: <?= htmlspecialchars($student['semester_masuk']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Status Judul</h6>
                <?php if ($titleStatus): ?>
                    <span class="badge <?= htmlspecialchars($titleStatus['badge']) ?> mb-2"><?= htmlspecialchars($titleStatus['label']) ?></span>
                    <p class="mb-0 small text-muted"><?= htmlspecialchars($titleStatus['detail']) ?></p>
                <?php else: ?>
                    <span class="badge bg-secondary mb-2">Belum Mengajukan</span>
                    <p class="mb-0 small text-muted">Silakan ajukan judul skripsi Anda.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase">Langkah Berikutnya</h6>
                <h4 class="fw-bold mb-2"><?= htmlspecialchars($nextAction) ?></h4>
                <?php if ($nextEvent): ?>
                    <p class="mb-0 small text-muted">Jadwal terdekat: <?= htmlspecialchars($eventLabels[$nextEvent['type']] ?? $nextEvent['type']) ?> pada <?= date('d M Y', strtotime($nextEvent['scheduled_date'])) . ' ' . substr($nextEvent['scheduled_time'], 0, 5) ?></p>
                <?php elseif (stripos($nextAction, 'judul') !== false): ?>
                    <p class="mb-0 small text-muted">Tunggu validasi Kombi. Belum ada jadwal berikutnya sampai judul diajukan/diterima.</p>
                <?php elseif (stripos($nextAction, 'lulus') !== false): ?>
                    <p class="mb-0 small text-muted">Selamat! Semua tahapan selesai dan status Anda Lulus.</p>
                <?php else: ?>
                    <p class="mb-0 small text-muted">Belum ada jadwal ujian/seminar berikutnya.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-route"></i> Progres Tahapan Skripsi</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <div class="stage-timeline">
                    <?php foreach ($stageStatus as $stage): ?>
                        <?php
                            $badge = $stage['status']['badge'] ?? 'bg-secondary';
                            $cssClass = 'todo';
                            if (str_contains($badge, 'success')) {
                                $cssClass = 'completed';
                            } elseif (str_contains($badge, 'warning') || str_contains($badge, 'info') || str_contains($badge, 'primary')) {
                                $cssClass = 'pending';
                            }
                        ?>
                        <div class="stage-item <?= $cssClass ?>">
                            <h6 class="mb-2"><?= htmlspecialchars($stage['label']) ?></h6>
                            <?php if (!empty($stage['date'])): ?>
                                <p class="mb-1 small"><strong>Tanggal:</strong> <?= htmlspecialchars($stage['date']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($stage['pembimbing'])): ?>
                                <p class="mb-1 small"><strong>Pembimbing:</strong> <?= htmlspecialchars(implode(', ', $stage['pembimbing'])) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($stage['penguji'])): ?>
                                <p class="mb-0 small"><strong>Penguji:</strong> <?= htmlspecialchars(implode(', ', $stage['penguji'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-5">
                <div class="alert alert-info">
                    <strong>Tips:</strong> pastikan Anda aktif berdiskusi dengan pembimbing dan segera mengunggah dokumen pendukung jika diminta.
                </div>
                <div class="d-grid gap-2">
                    <a href="/titles/submit" class="btn btn-primary"><i class="fas fa-file-pen me-2"></i>Kelola Pengajuan Judul</a>
                    <a href="/timeline" class="btn btn-outline-secondary"><i class="fas fa-timeline me-2"></i>Lihat Timeline Lengkap</a>
                    <a href="/profile" class="btn btn-outline-secondary"><i class="fas fa-id-card me-2"></i>Profil Mahasiswa</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">Data mahasiswa tidak ditemukan. Silakan hubungi Kombi untuk memastikan akun Anda terhubung dengan data mahasiswa.</div>
<?php endif; ?>
