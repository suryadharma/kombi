<?php
$summaryCards = $summaryCards ?? [];
$upcomingEvents = $upcomingEvents ?? [];
$pendingEvaluations = $pendingEvaluations ?? [];
$quickLinks = $quickLinks ?? [];
$eventLabels = [
    'SEMPRO' => 'Seminar Proposal',
    'SEMHAS' => 'Seminar Hasil',
    'PRA_UJIAN' => 'Pra-Ujian',
    'UJIAN_SKRIPSI' => 'Ujian Skripsi'
];
?>

<style>
.dashboard-icon {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}
</style>

<div class="row align-items-center mb-4">
    <div class="col-12">
        <h2 class="mb-1"><i class="fas fa-user-graduate"></i> Dashboard Dosen Penguji</h2>
        <p class="text-muted mb-0">Atur jadwal ujian dan pastikan seluruh penilaian penguji tercatat.</p>
    </div>
</div>

<?php if (!empty($summaryCards)): ?>
<div class="row g-3 mb-4">
    <?php foreach ($summaryCards as $card): ?>
    <?php $iconClass = $card['iconClass'] ?? ('bg-' . ($card['variant'] ?? 'primary') . ' text-white'); ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center">
                <span class="dashboard-icon rounded-circle <?= htmlspecialchars($iconClass) ?>">
                    <i class="fas <?= htmlspecialchars($card['icon']) ?>"></i>
                </span>
                <div class="ms-3">
                    <p class="text-muted mb-1 small text-uppercase"><?= htmlspecialchars($card['label']) ?></p>
                    <h3 class="mb-0 fw-bold"><?= htmlspecialchars($card['value']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Jadwal Terdekat</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingEvents)): ?>
                    <div class="alert alert-info mb-0">Tidak ada jadwal ujian mendatang untuk Anda.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Tahap</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingEvents as $event): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($event['name']) ?></strong><br><?= htmlspecialchars($event['nim']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($eventLabels[$event['type']] ?? $event['type']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($event['scheduled_date'])) . ' ' . substr($event['scheduled_time'], 0, 5) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Penilaian yang Belum Diisi</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pendingEvaluations)): ?>
                    <div class="alert alert-success mb-0">Semua nilai penguji telah Anda lengkapi.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($pendingEvaluations as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($item['name']) ?></strong> - <?= htmlspecialchars($item['nim']) ?><br>
                                <small class="text-muted">Tahap: <?= htmlspecialchars($eventLabels[$item['type']] ?? $item['type']) ?></small>
                            </div>
                            <span class="badge bg-warning text-dark">Segera nilai</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($quickLinks)): ?>
<div class="row g-3">
    <?php foreach ($quickLinks as $link): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas <?= htmlspecialchars($link['icon']) ?> text-<?= htmlspecialchars($link['variant']) ?> me-2"></i><?= htmlspecialchars($link['title']) ?></h5>
                <p class="card-text small text-muted"><?= htmlspecialchars($link['description']) ?></p>
                <a href="<?= htmlspecialchars($link['url']) ?>" class="btn btn-sm btn-outline-primary <?= !empty($link['disabled']) ? 'disabled' : '' ?>">Akses</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
