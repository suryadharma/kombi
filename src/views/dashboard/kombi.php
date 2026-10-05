<?php
$summaryCards = $summaryCards ?? [];
$pendingTitles = $pendingTitles ?? [];
$upcomingEvents = $upcomingEvents ?? [];
$angkatanStats = $angkatanStats ?? [];
$shortcuts = $shortcuts ?? [];
$error = $error ?? null;

$activeAngkatan = Settings::getActiveAngkatan();
?>

<div class="row">
    <div class="col-12">
        <h2>Dashboard Kombi</h2>
        <p class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>!</p>
        <p class="text-muted small">Catatan: Statistik di bawah ini hanya mencakup mahasiswa dengan status selain "LULUS" dari angkatan aktif.</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-file-circle-question me-2"></i>Judul Menunggu Verifikasi</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingTitles)): ?>
                <div class="text-center p-3 text-muted">
                    Tidak ada judul yang menunggu verifikasi
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingTitles as $title): ?>
                            <tr onclick="window.location='/titles/verify/<?= htmlspecialchars($title['id']) ?>'"
                                style="cursor: pointer;"
                                class="dashboard-clickable-row"
                                title="Klik untuk verifikasi judul ini">
                                <td><?= htmlspecialchars($title['nim']) ?></td>
                                <td><?= htmlspecialchars($title['name']) ?></td>
                                <td>
                                    <?php
                                        $titleText = (string)$title['title'];
                                        if (function_exists('mb_substr')) {
                                            $titlePreview = mb_substr($titleText, 0, 50);
                                            $isTrimmed = mb_strlen($titleText) > 50;
                                        } else {
                                            $titlePreview = substr($titleText, 0, 50);
                                            $isTrimmed = strlen($titleText) > 50;
                                        }
                                        echo htmlspecialchars($isTrimmed ? $titlePreview . '…' : $titlePreview);
                                    ?>
                                </td>
                                <td><?= date('d M Y', strtotime($title['submitted_at'])) ?></td>
                                <td class="text-end">
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <style>
                .dashboard-clickable-row {
                    transition: background-color 0.2s ease, transform 0.1s ease;
                }
                .dashboard-clickable-row:hover {
                    background-color: rgba(13, 110, 253, 0.05) !important;
                    transform: scale(1.005);
                }
                </style>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-calendar-days me-2"></i>Jadwal Mendatang</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingEvents)): ?>
                <div class="text-center p-3 text-muted">
                    Tidak ada jadwal mendatang
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Agenda</th>
                                <th>Jadwal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingEvents as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['nim']) ?></td>
                                <td><?= htmlspecialchars($event['name']) ?></td>
                                <td><?= htmlspecialchars($event['type']) ?></td>
                                <td><?= date('d M Y H:i', strtotime($event['scheduled_date'] . ' ' . $event['scheduled_time'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (empty($activeAngkatan)): ?>
<div class="alert alert-warning">
    <h4>Perhatian!</h4>
    <p>Belum ada angkatan yang diaktifkan. Silakan <a href="/settings">aktifkan angkatan</a> untuk melihat data mahasiswa.</p>
</div>
<?php else: ?>
<!-- Summary Cards -->
<div class="row">
    <?php if (!empty($summaryCards)): ?>
        <?php foreach ($summaryCards as $card): ?>
        <div class="col-md-6 col-lg-4 col-xl-2-4 mb-3">
            <div class="card border-0 shadow-sm h-100 summary-card summary-card-<?= htmlspecialchars($card['variant']) ?>">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center mb-2">
                        <div class="rounded-circle summary-card-icon <?= htmlspecialchars($card['iconClass']) ?> d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas <?= htmlspecialchars($card['icon']) ?> fa-lg"></i>
                        </div>
                    </div>
                    <h5 class="card-title mb-1 summary-card-value"><?= htmlspecialchars($card['value']) ?></h5>
                    <p class="card-text small mb-0 summary-card-label"><?= htmlspecialchars($card['label']) ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                Belum ada data ringkasan yang bisa ditampilkan. Tambahkan atau impor mahasiswa untuk mulai melihat statistik.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Shortcut Cards -->
<div class="row mb-4">
    <?php if (!empty($shortcuts)): ?>
        <?php foreach ($shortcuts as $shortcut): ?>
        <div class="col-md-6 col-lg-4 col-xl-2-4 mb-3">
            <a href="<?= htmlspecialchars($shortcut['url']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 bg-<?= htmlspecialchars($shortcut['variant']) ?> text-white shortcut-card">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center mb-2">
                            <div class="rounded-circle shortcut-card-icon d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas <?= htmlspecialchars($shortcut['icon']) ?> fa-lg"></i>
                            </div>
                        </div>
                        <h5 class="card-title mb-1 shortcut-card-title"><?= htmlspecialchars($shortcut['title']) ?></h5>
                        <p class="card-text small mb-0 shortcut-card-text"><?= htmlspecialchars($shortcut['description']) ?></p>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-secondary">
                Tidak ada pintasan yang aktif saat ini. Hubungi administrator jika Anda membutuhkan akses cepat ke fitur tertentu.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Tables Section -->

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Ringkasan Tahapan Per Angkatan</h5>
                <span class="text-muted small">Hanya menampilkan angkatan aktif jika sudah ditentukan</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($angkatanStats)): ?>
                <div class="text-center p-3 text-muted">
                    Belum ada data angkatan yang dapat dirangkum.
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Angkatan</th>
                                <th class="text-center">Total Mahasiswa</th>
                                <th class="text-center">Belum Ajukan Judul</th>
                                <th class="text-center">Sudah Terjadwal Sempro</th>
                                <th class="text-center">Sudah Terjadwal Semhas</th>
                                <th class="text-center">Lulus</th>
                                <th class="text-center">Mengulang Ujian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($angkatanStats as $stat): ?>
                            <tr>
                                <td><?= htmlspecialchars($stat['angkatan']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-dark"><?= htmlspecialchars($stat['total']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($stat['no_title']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($stat['sempro']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= htmlspecialchars($stat['semhas']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= htmlspecialchars($stat['lulus']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= htmlspecialchars($stat['mengulang']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
