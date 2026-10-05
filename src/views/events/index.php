<?php
$events = $events ?? [];
$stageOptions = $stageOptions ?? [];
$statusOptions = $statusOptions ?? [];
$angkatanList = $angkatanList ?? [];
$activeAngkatan = $activeAngkatan ?? [];
$filters = $filters ?? ['stage' => '', 'status' => '', 'angkatan' => '', 'search' => ''];
$success = $success ?? null;
$error = $error ?? null;
?>

<div class="row mb-3 align-items-center">
    <div class="col-lg-8 col-md-7">
        <h2 class="mb-1"><i class="fas fa-calendar-check"></i> Penjadwalan Seminar & Ujian</h2>
        <p class="text-muted mb-0">Atur tanggal, waktu, dan ruang untuk setiap tahap skripsi mahasiswa. Undangan penguji eksternal dapat dibuat dari sini.</p>
    </div>
    <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
        <a href="/events/create" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Jadwalkan Tahap Baru
        </a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="hide_completed" value="0">
            <div class="col-md-3">
                <label for="filterStage" class="form-label">Tahap</label>
                <select class="form-select" id="filterStage" name="stage">
                    <option value="">Semua Tahap</option>
                    <?php foreach ($stageOptions as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= $filters['stage'] === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterStatus" class="form-label">Status</label>
                <select class="form-select" id="filterStatus" name="status">
                    <option value="">Semua Status</option>
                    <?php foreach ($statusOptions as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterAngkatan" class="form-label">Angkatan</label>
                <select class="form-select" id="filterAngkatan" name="angkatan">
                    <option value="">Semua <?= !empty($activeAngkatan) ? 'Angkatan Aktif' : 'Angkatan' ?></option>
                    <?php foreach ($angkatanList as $angkatan): ?>
                        <option value="<?= htmlspecialchars($angkatan) ?>" <?= $filters['angkatan'] === $angkatan ? 'selected' : '' ?>>
                            <?= htmlspecialchars($angkatan) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Mahasiswa</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="NIM atau nama..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-md-3 col-lg-2">
                <div class="form-check mt-4 pt-2">
                    <input class="form-check-input" type="checkbox" id="hideCompleted" name="hide_completed" value="1"
                        <?= !empty($filters['hide_completed']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="hideCompleted">
                        Sembunyikan yang sudah selesai
                    </label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-md-1">
                <a href="/events" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Penjadwalan</h5>
        <div>
            <span class="badge bg-primary me-2"><?= count($studentSchedules) ?> mahasiswa</span>
            <span class="badge bg-info text-dark"><?= count($events) ?> jadwal</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Angkatan</th>
                        <th>Dosen Pembimbing & Penguji</th>
                        <th>Ringkasan Tahap</th>
                        <th>Jadwal Berikutnya</th>
                        <th>Status Keseluruhan</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($studentSchedules)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada penjadwalan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($studentSchedules as $student): ?>
                            <?php
                                $summaryBadges = [];
                                foreach ($student['stages'] as $stageEvent) {
                                    $badgeClass = 'bg-secondary';
                                    if ($stageEvent['status'] === 'MENUNGGU') {
                                        $badgeClass = 'bg-warning text-dark';
                                    } elseif ($stageEvent['status'] === 'SELESAI') {
                                        $badgeClass = 'bg-success';
                                    } elseif ($stageEvent['status'] === 'BATAL') {
                                        $badgeClass = 'bg-danger';
                                    }
                                    $summaryBadges[] = '<span class="badge ' . $badgeClass . ' me-1 mb-1">' . htmlspecialchars($stageEvent['stage_label'] ?? '-') . '</span>';
                                }
                                $overallStatus = 'Belum Dijadwalkan';
                                $overallBadge = 'bg-secondary';
                                if ($student['pending_count'] > 0) {
                                    $overallStatus = 'Menunggu';
                                    $overallBadge = 'bg-warning text-dark';
                                } elseif (!empty($student['stages'])) {
                                    $overallStatus = 'Selesai';
                                    $overallBadge = 'bg-success';
                                }
                                $nextSchedule = '-';
                                if (!empty($student['next_schedule'])) {
                                    try {
                                        $nextDate = new DateTime($student['next_schedule']);
                                        $nextSchedule = htmlspecialchars($student['next_stage'] ?? '-') . '<br><span class="text-muted">' . $nextDate->format('d M Y H:i') . ' WIB</span>';
                                    } catch (Exception $e) {
                                        $nextSchedule = htmlspecialchars($student['next_stage'] ?? '-');
                                    }
                                }
                                $collapseId = 'event-detail-' . $student['student_id'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($student['nim']) ?></strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($student['student_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($student['angkatan']) ?></span>
                                </td>
                                <td>
                                    <small class="d-block mb-1">
                                        <strong>Pembimbing:</strong><br>
                                        <?php if (!empty($student['pembimbing_1'])): ?>
                                            <span class="badge bg-primary me-1">P1</span> <?= htmlspecialchars($student['pembimbing_1']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ditetapkan</span>
                                        <?php endif; ?>
                                        <?php if (!empty($student['pembimbing_2'])): ?>
                                            <br><span class="badge bg-primary me-1">P2</span> <?= htmlspecialchars($student['pembimbing_2']) ?>
                                        <?php endif; ?>
                                    </small>
                                    <small class="d-block">
                                        <strong>Penguji:</strong><br>
                                        <?php if (!empty($student['penguji_1'])): ?>
                                            <span class="badge bg-info text-dark me-1">Ketua</span> <?= htmlspecialchars($student['penguji_1']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ditetapkan</span>
                                        <?php endif; ?>
                                        <?php if (!empty($student['penguji_2'])): ?>
                                            <br><span class="badge bg-info text-dark me-1">Anggota 1</span> <?= htmlspecialchars($student['penguji_2']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($student['penguji_3'])): ?>
                                            <br><span class="badge bg-info text-dark me-1">Anggota 2</span> <?= htmlspecialchars($student['penguji_3']) ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                                <td><?= implode('', $summaryBadges) ?: '<span class="text-muted small">Belum ada jadwal</span>' ?></td>
                                <td><?= $nextSchedule ?></td>
                                <td><span class="badge <?= $overallBadge ?>"><?= htmlspecialchars($overallStatus) ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                                        <i class="fas fa-info-circle"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="<?= $collapseId ?>">
                                <td colspan="7">
                                    <div class="card card-body">
                                        <?php if (empty($student['stages'])): ?>
                                            <p class="text-muted mb-0">Belum ada penjadwalan untuk mahasiswa ini.</p>
                                        <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Tahap</th>
                                                        <th>Jadwal</th>
                                                        <th>Ruang</th>
                                                        <th>Penguji Eksternal</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($student['stages'] as $stageEvent): ?>
                                                        <?php
                                                            $stageStatusBadge = 'bg-secondary';
                                                            if ($stageEvent['status'] === 'MENUNGGU') {
                                                                $stageStatusBadge = 'bg-warning text-dark';
                                                            } elseif ($stageEvent['status'] === 'SELESAI') {
                                                                $stageStatusBadge = 'bg-success';
                                                            } elseif ($stageEvent['status'] === 'BATAL') {
                                                                $stageStatusBadge = 'bg-danger';
                                                            }
                                                        ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($stageEvent['stage_label'] ?? '-') ?></td>
                                                            <td>
                                                                <?= htmlspecialchars(date('d M Y', strtotime($stageEvent['scheduled_date']))) ?><br>
                                                                <span class="text-muted"><?= htmlspecialchars(substr($stageEvent['scheduled_time'], 0, 5)) ?> WIB</span>
                                                            </td>
                                                            <td><?= htmlspecialchars($stageEvent['room']) ?></td>
                                                            <td>
                                                                <?php if (!empty($stageEvent['external_token'])): ?>
                                                                    <div class="text-muted small">
                                                                        Username: <strong><?= htmlspecialchars($stageEvent['external_token']['username']) ?></strong><br>
                                                                        Token: <span class="font-monospace"><?= htmlspecialchars($stageEvent['external_token']['token']) ?></span><br>
                                                                        Berlaku: <?= htmlspecialchars(date('d M Y H:i', strtotime($stageEvent['external_token']['expires_at']))) ?> WIB
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted small">Tidak ada</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><span class="badge <?= $stageStatusBadge ?>"><?= htmlspecialchars($stageEvent['status']) ?></span></td>
                                                            <td class="text-center">
                                                                <a href="/events/<?= htmlspecialchars($stageEvent['id']) ?>/edit" class="btn btn-sm btn-outline-primary mb-1">
                                                                    <i class="fas fa-pen"></i>
                                                                </a>
                                                                <form method="POST" action="/events/<?= htmlspecialchars($stageEvent['id']) ?>/delete" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus penjadwalan ini?');">
                                                                    <?= Csrf::field(); ?>
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="text-muted small mt-3 mb-0">
            Catatan: Token penguji eksternal dapat dibuat dari form penjadwalan saat membuat atau mengedit jadwal.
        </p>
    </div>
</div>
