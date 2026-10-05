<?php
$allAngkatan = $allAngkatan ?? [];
$activeAngkatan = $activeAngkatan ?? [];
$error = $error ?? null;
$slaThresholds = $slaThresholds ?? Settings::getSlaThresholds();
$stageProgressSla = $stageProgressSla ?? Settings::getStageProgressSlaConfig();
$scoreVisibility = $scoreVisibility ?? Settings::getScoreVisibilityConfig();
$emailQueueStats = $emailQueueStats ?? null;
$userRole = $userRole ?? '';
$evaluationSettings = $evaluationSettings ?? ['edit_window_hours' => 48];
$emailSettings = $emailSettings ?? [];

// General App Settings (without logo settings - kept hardcoded)
$appSettings = [
    'app_name' => Settings::get('app_name', 'KomBi-TIP Apps'),
    'app_full_name' => Settings::get('app_full_name', 'Sistem Komisi Bimbingan Skripsi'),
    'app_short_name' => Settings::get('app_short_name', 'KomBi-TIP'),
    'org_prodi' => Settings::get('org_prodi', 'Program Studi Teknologi Industri Pertanian'),
    'org_fakultas' => Settings::get('org_fakultas', 'Fakultas Teknologi Pertanian'),
    'org_universitas' => Settings::get('org_universitas', 'Universitas Jember'),
    'org_location' => Settings::get('org_location', 'Jember'),
    'app_footer_text' => Settings::get('app_footer_text', 'KomBi-TIP Apps'),
    'app_developer_name' => Settings::get('app_developer_name', 'BS'),
    'app_developer_url' => Settings::get('app_developer_url', 'https://www.suryadharma.work/'),
    'app_admin_contact' => Settings::get('app_admin_contact', 'admin@kombi.unej.ac.id'),
];

$slaStageLabels = [
    'sempro' => 'Seminar Proposal → Entri Nilai',
    'semhas' => 'Seminar Hasil → Entri Nilai',
    'pra-ujian' => 'Pra-Ujian → Entri Nilai',
    'ujian' => 'Ujian Skripsi → Entri Nilai'
];
$progressStageLabels = [
    'sempro_semhas' => 'Seminar Proposal → Seminar Hasil',
    'semhas_pra-ujian' => 'Seminar Hasil → Pra-Ujian',
    'pra-ujian_ujian' => 'Pra-Ujian → Ujian Skripsi'
];
$scoreStageLabels = [
    'sempro' => 'Nilai Seminar Proposal',
    'semhas' => 'Nilai Seminar Hasil',
    'pra-ujian' => 'Nilai Pra-Ujian',
    'ujian' => 'Nilai Ujian Skripsi',
    'final_score' => 'Nilai Akhir Skripsi'
];
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Section: Umum (General Settings) -->

<?php if ($userRole === 'superadmin'): ?>
<div class="row mb-4">
    <div class="col-12">
        <h2>🌐 Pengaturan Umum Aplikasi</h2>
        <p class="text-muted">Konfigurasi identitas aplikasi, organisasi, dan tampilan visual.</p>
        <div class="alert alert-info">
            <strong>🔒 Superadmin Only:</strong> Konfigurasi ini hanya dapat diakses oleh superadmin.
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="/settings/update" id="settingsForm">
            <?= Csrf::field(); ?>
            <input type="hidden" name="section" id="sectionInput" value="">
            
            <!-- Nama Aplikasi -->
            <div class="mb-4">
                <h5 class="mb-3">Nama Aplikasi</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nama Aplikasi (Singkat)</label>
                        <input type="text" class="form-control" name="app[app_name]" value="<?= htmlspecialchars($appSettings['app_name']) ?>" placeholder="KomBi-TIP Apps">
                        <small class="text-muted">Nama pendek untuk navbar dan title</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="app[app_full_name]" value="<?= htmlspecialchars($appSettings['app_full_name']) ?>" placeholder="Sistem Komisi Bimbingan Skripsi">
                        <small class="text-muted">Nama lengkap aplikasi</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Pendek</label>
                        <input type="text" class="form-control" name="app[app_short_name]" value="<?= htmlspecialchars($appSettings['app_short_name']) ?>" placeholder="KomBi-TIP">
                        <small class="text-muted">Singkatan/akronim</small>
                    </div>
                </div>
            </div>
            
            <!-- Informasi Organisasi -->
            <div class="mb-4">
                <h5 class="mb-3">Informasi Organisasi</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Program Studi</label>
                        <input type="text" class="form-control" name="app[org_prodi]" value="<?= htmlspecialchars($appSettings['org_prodi']) ?>" placeholder="Program Studi...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fakultas</label>
                        <input type="text" class="form-control" name="app[org_fakultas]" value="<?= htmlspecialchars($appSettings['org_fakultas']) ?>" placeholder="Fakultas...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Universitas</label>
                        <input type="text" class="form-control" name="app[org_universitas]" value="<?= htmlspecialchars($appSettings['org_universitas']) ?>" placeholder="Universitas...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kota/Lokasi</label>
                        <input type="text" class="form-control" name="app[org_location]" value="<?= htmlspecialchars($appSettings['org_location']) ?>" placeholder="Jember">
                    </div>
                </div>
            </div>
            
            <!-- Footer & Contact -->
            <div class="mb-4">
                <h5 class="mb-3">Footer & Kontak</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Teks Footer</label>
                        <input type="text" class="form-control" name="app[app_footer_text]" value="<?= htmlspecialchars($appSettings['app_footer_text']) ?>" placeholder="KomBi-TIP Apps">
                        <small class="text-muted">Teks yang muncul di footer</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Developer</label>
                        <input type="text" class="form-control" name="app[app_developer_name]" value="<?= htmlspecialchars($appSettings['app_developer_name']) ?>" placeholder="BS">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">URL Developer</label>
                        <input type="text" class="form-control" name="app[app_developer_url]" value="<?= htmlspecialchars($appSettings['app_developer_url']) ?>" placeholder="https://...">
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-12">
                        <label class="form-label">Kontak Admin</label>
                        <input type="email" class="form-control" name="app[app_admin_contact]" value="<?= htmlspecialchars($appSettings['app_admin_contact']) ?>" placeholder="admin@kombi.unej.ac.id">
                        <small class="text-muted">Email kontak admin aplikasi</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="saveAppSettingsBtn">
            <i class="fas fa-save me-1"></i> Simpan Pengaturan Aplikasi
        </button>
    </div>
</div>
<?php endif; // End of superadmin check for general settings ?>

<hr class="my-4">

<!-- Section: Angkatan (Active Angkatan Settings) -->
<div class="row">
    <div class="col-12">
        <h2>Pengaturan Angkatan Aktif</h2>
        <p class="text-muted">Pilih angkatan yang sedang dalam masa skripsi untuk difokuskan dalam aplikasi.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/settings/update" id="settingsForm2">
            <?= Csrf::field(); ?>
            <input type="hidden" name="section" id="sectionInput2" value="">
            
            <div class="mb-4">
                <h4 class="mb-3">Pengaturan Angkatan Aktif</h4>
                
                <div class="mb-3">
                <div class="row">
                    <?php foreach ($allAngkatan as $angkatan): ?>
                    <div class="col-md-3 col-sm-4 col-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="active_angkatan[]" value="<?= htmlspecialchars($angkatan) ?>" id="angkatan_<?= htmlspecialchars($angkatan) ?>" <?= in_array($angkatan, $activeAngkatan) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="angkatan_<?= htmlspecialchars($angkatan) ?>">
                                Angkatan <?= htmlspecialchars($angkatan) ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if (!empty($activeAngkatan)): ?>
            <div class="alert alert-info">
                <h5>Statistik Angkatan Aktif:</h5>
                <?php
                // Hitung jumlah mahasiswa dari angkatan aktif
                $database = new Database();
                $db = $database->getConnection();
                
                $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
                $totalQuery = "SELECT COUNT(*) FROM students WHERE angkatan IN ($placeholders)";
                $totalStmt = $db->prepare($totalQuery);
                $totalStmt->execute($activeAngkatan);
                $totalStudents = $totalStmt->fetchColumn();
                
                $activeQuery = "SELECT COUNT(*) FROM students WHERE angkatan IN ($placeholders) AND status != 'LULUS'";
                $activeStmt = $db->prepare($activeQuery);
                $activeStmt->execute($activeAngkatan);
                $activeStudents = $activeStmt->fetchColumn();
                
                $lulusQuery = "SELECT COUNT(*) FROM students WHERE angkatan IN ($placeholders) AND status = 'LULUS'";
                $lulusStmt = $db->prepare($lulusQuery);
                $lulusStmt->execute($activeAngkatan);
                $lulusStudents = $lulusStmt->fetchColumn();
                ?>
                <ul>
                    <li>Total mahasiswa dari angkatan aktif (<?= implode(', ', $activeAngkatan) ?>): <strong><?= $totalStudents ?></strong></li>
                    <li>Mahasiswa aktif (belum lulus): <strong><?= $activeStudents ?></strong></li>
                    <li>Mahasiswa sudah lulus: <strong><?= $lulusStudents ?></strong></li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <h5>Informasi:</h5>
                <p>Dengan mengaktifkan angkatan tertentu, seluruh fitur aplikasi akan fokus pada mahasiswa dari angkatan yang dipilih. 
                Hanya mahasiswa dari angkatan aktif yang akan ditampilkan di dashboard, monitoring, dan laporan.</p>
                <p>Mahasiswa dari angkatan yang tidak aktif masih dapat diakses melalui filter khusus di halaman daftar mahasiswa.</p>
            </div>

            </div>
            
            <hr class="my-4">

            <div class="mb-4">
                <h4 class="mb-3">Visibilitas Nilai untuk Mahasiswa</h4>
                <p class="text-muted">Aktifkan tahap yang boleh dilihat mahasiswa. Saat aktif, mahasiswa dapat melihat rekap nilai dari semua dosen serta mencetak PDF.</p>
                <div class="row">
                    <?php foreach ($scoreStageLabels as $stageKey => $label): ?>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="score_visibility_<?= htmlspecialchars(str_replace('-', '_', $stageKey)) ?>"
                                       name="score_visibility[<?= htmlspecialchars($stageKey) ?>]"
                                       <?= !empty($scoreVisibility[$stageKey]) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="score_visibility_<?= htmlspecialchars(str_replace('-', '_', $stageKey)) ?>">
                                    <?= htmlspecialchars($label) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="small text-muted">Pengaturan ini tidak mempengaruhi akses dosen/kombi, hanya menambah akses baca bagi mahasiswa pada timeline.</div>
                <div class="alert alert-info mt-2">
                    <small><i class="fas fa-info-circle me-1"></i><strong>Nilai Akhir Skripsi</strong> hanya akan muncul jika mahasiswa sudah menyelesaikan semua tahap penilaian (Sempro, Semhas, Pra-Ujian, dan Ujian Skripsi).</small>
                </div>
            </div>

            <div class="mb-4">
                <h4 class="mb-3">Konfigurasi SLA Entri Nilai</h4>
                <p class="text-muted">Atur batas waktu maksimal (dalam jam) antara pelaksanaan tahap dan pengisian nilai oleh dosen. Jika melebihi batas, status akan dianggap "Lewat SLA".</p>
                <div class="row g-3">
                    <?php foreach ($slaStageLabels as $stageKey => $label): ?>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label"><?= htmlspecialchars($label) ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" min="1" name="sla_threshold[<?= htmlspecialchars($stageKey) ?>]" value="<?= htmlspecialchars($slaThresholds[$stageKey] ?? 72) ?>">
                                <span class="input-group-text">jam</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-4">
                <h4 class="mb-3">Target Durasi Antar Tahap</h4>
                <p class="text-muted">Nilai di bawah akan digunakan pada laporan pelacakan untuk menandai mahasiswa yang melewati batas antar tahapan.</p>
                <div class="row g-3">
                    <?php foreach ($progressStageLabels as $progressKey => $label): ?>
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label"><?= htmlspecialchars($label) ?></label>
                            <div class="input-group">
                                <input type="number" class="form-control" min="1" name="progress_sla[<?= htmlspecialchars($progressKey) ?>]" value="<?= htmlspecialchars($stageProgressSla[$progressKey] ?? 30) ?>">
                                <span class="input-group-text">hari</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" id="saveGeneralSettingsBtn">
                    <i class="fas fa-save me-1"></i> Simpan Pengaturan Umum
                </button>
            </div>
        </form>
    </div>
</div>

<hr class="my-4">

<!-- Section: Beban Dosen (Workload Settings) -->
<?php if ($userRole === 'kombi' || $userRole === 'superadmin'): ?>
<div class="row mt-4">
    <div class="col-12">
        <h2>📊 Pengaturan Beban Dosen</h2>
        <p class="text-muted">Atur kuota lunak beban dosen untuk laporan beban kerja.</p>
        <div class="alert alert-info">
            <strong>ℹ️ Info:</strong> Kuota lunak digunakan sebagai acuan di laporan beban dosen. Dosen dengan beban melebihi kuota ini akan ditandai.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/settings/update" id="settingsFormWorkload">
            <?= Csrf::field(); ?>
            <input type="hidden" name="section" id="sectionInputWorkload" value="">
            
            <div class="mb-4">
                <h4 class="mb-3">Kuota Lunak Beban Dosen</h4>
                <p class="text-muted">Batas maksimal mahasiswa yang dapat dibimbing/diuji oleh setiap dosen.</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Maksimal Mahasiswa Bimbingan</label>
                        <div class="input-group">
                            <input type="number" class="form-control" min="1" max="50" name="workload[max_pembimbing]"
                                   value="<?= htmlspecialchars(Settings::get('max_pembimbing', 8)) ?>" required>
                            <span class="input-group-text">mahasiswa</span>
                        </div>
                        <small class="text-muted">Kuota lunak untuk pembimbing (default: 8)</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Maksimal Mahasiswa Pengujian</label>
                        <div class="input-group">
                            <input type="number" class="form-control" min="1" max="50" name="workload[max_penguji]"
                                   value="<?= htmlspecialchars(Settings::get('max_penguji', 10)) ?>" required>
                            <span class="input-group-text">mahasiswa</span>
                        </div>
                        <small class="text-muted">Kuota lunak untuk penguji (default: 10)</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="saveWorkloadSettingsBtn">
            <i class="fas fa-save me-1"></i> Simpan Pengaturan Beban Dosen
        </button>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <strong>⚠️ Akses Ditolak:</strong> Anda tidak memiliki akses ke pengaturan beban dosen.
</div>
<?php endif; // End of kombi/superadmin check for workload settings ?>

<hr class="my-4">

<!-- Section: Edit Nilai (Evaluation Edit Settings) -->
<?php if ($userRole === 'kombi' || $userRole === 'superadmin'): ?>
<div class="row mt-4">
    <div class="col-12">
        <h2>🔐 Pengaturan Edit Nilai</h2>
        <p class="text-muted">Konfigurasi jendela waktu edit nilai dan notifikasi ke mahasiswa.</p>
        <div class="alert alert-info">
            <strong>ℹ️ Info:</strong> Pengaturan ini mengontrol bagaimana dosen dapat mengedit nilai yang sudah disubmit.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/settings/update" id="settingsFormEvaluation">
            <?= Csrf::field(); ?>
            <input type="hidden" name="section" id="sectionInputEvaluation" value="">
            
            <div class="mb-4">
                <h4 class="mb-3">Jendela Waktu Edit Nilai</h4>
                <p class="text-muted">Dosen dapat mengedit nilai dalam jendela waktu setelah submit. Setelah jendela waktu berakhir, nilai akan terkunci secara otomatis.</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Durasi Jendela Edit</label>
                        <div class="input-group">
                            <input type="number" class="form-control" min="1" max="168" name="evaluation[edit_window_hours]"
                                   value="<?= htmlspecialchars($evaluationSettings['edit_window_hours'] ?? 48) ?>" required>
                            <span class="input-group-text">jam</span>
                        </div>
                        <small class="text-muted">Maksimal 168 jam (7 hari). Default: 48 jam</small>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="saveEvaluationSettingsBtn">
            <i class="fas fa-save me-1"></i> Simpan Pengaturan Edit Nilai
        </button>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <strong>⚠️ Akses Ditolak:</strong> Anda tidak memiliki akses ke pengaturan edit nilai.
</div>
<?php endif; // End of kombi/superadmin check for evaluation settings ?>

<hr class="my-4">

<!-- Section: Email (Email Configuration) -->
<?php if ($userRole === 'superadmin'): ?>
<div class="row mt-4">
    <div class="col-12">
        <h2>⚙️ Konfigurasi Email (Notifikasi ke Dosen)</h2>
        <p class="text-muted">Kirim otomatis PDF hasil penilaian ke dosen pembimbing dan penguji via Gmail.</p>
        <div class="alert alert-warning">
            <strong>🔒 Superadmin Only:</strong> Konfigurasi email hanya dapat diakses oleh superadmin.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/settings/update" id="settingsForm3">
            <?= Csrf::field(); ?>
            <input type="hidden" name="section" id="sectionInput3" value="">
            
            <div class="mb-4">
                <h4 class="mb-3">⚙️ Konfigurasi Email</h4>
                <p class="text-muted mb-3">Kirim otomatis PDF hasil penilaian ke dosen pembimbing dan penguji via Gmail.</p>
                
                <div class="alert alert-info">
                    <strong>📧 Cara Setting Gmail:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Buka <a href="https://myaccount.google.com/security" target="_blank">Google Account Security</a></li>
                       >Aktifkan <strong>2-Step Verification</strong></li>
                       >Pilih <strong>App Passwords</strong> → <strong>Select App</strong> (pilih "Mail")</li>
                       >Klik <strong>Generate</strong> → Copy 16 karakter password</li>
                       ><strong>Paste TANPA spasi</strong> di bawah</li>
                    </ol>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   id="email_enabled"
                                   name="email[enabled]"
                                   value="1"
                                   <?= !empty($emailSettings['enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="email_enabled">
                                Aktifkan Email Notifikasi
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Email Gmail Pengirim</label>
                        <input type="email" class="form-control" name="email[email_gmail]" value="<?= htmlspecialchars($emailSettings['email_gmail'] ?? '') ?>" placeholder="nama@gmail.com" required>
                        <small class="text-muted">Email Gmail yang akan mengirim notifikasi</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">App Password Gmail</label>
                        <input type="password" class="form-control" name="email[app_password]" value="<?= htmlspecialchars($emailSettings['app_password'] ?? '') ?>" placeholder="********" required>
                        <small class="text-muted">16 karakter, TANPA spasi</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SMTP Port</label>
                        <select class="form-select" name="email[port]" required>
                            <option value="587" <?= (isset($emailSettings['port']) && $emailSettings['port'] == '587') ? 'selected' : '' ?>>587 - TLS</option>
                            <option value="465" <?= (isset($emailSettings['port']) && $emailSettings['port'] == '465') ? 'selected' : '' ?>>465 - SSL</option>
                            <option value="25" <?= (isset($emailSettings['port']) && $emailSettings['port'] == '25') ? 'selected' : '' ?>>25 - None</option>
                        </select>
                        <small class="text-muted">Port SMTP (default: 587)</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Enkripsi</label>
                        <select class="form-select" name="email[encryption]" required>
                            <option value="tls" <?= (isset($emailSettings['encryption']) && $emailSettings['encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= (isset($emailSettings['encryption']) && $emailSettings['encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= (isset($emailSettings['encryption']) && $emailSettings['encryption'] == 'none') ? 'selected' : '' ?>>None</option>
                        </select>
                        <small class="text-muted">Enkripsi (default: TLS)</small>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-2">
                    <small>
                        <strong>⚠️ Tips:</strong> Jika gagal koneksi ke Gmail, coba ganti ke <strong>Port 465 dengan SSL</strong>.
                        Beberapa server memblokir port 587 atau memiliki masalah dengan TLS handshake.
                    </small>
                </div>

                <?php if ($emailQueueStats !== null): ?>
                <div class="alert alert-info mt-3">
                    <strong>📊 Status Queue:</strong>
                    Pending: <?= htmlspecialchars($emailQueueStats['pending'] ?? 0) ?> |
                    Processing: <?= htmlspecialchars($emailQueueStats['processing'] ?? 0) ?> |
                    Sent: <?= htmlspecialchars($emailQueueStats['sent'] ?? 0) ?> |
                    Failed: <?= htmlspecialchars($emailQueueStats['failed'] ?? 0) ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="testEmailBtn">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Email Test
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm ms-2" id="diagnoseEmailBtn" data-bs-toggle="modal" data-bs-target="#emailDiagnosticModal">
                        <i class="fas fa-stethoscope me-1"></i> Diagnosa Email
                    </button>
                    <span id="testEmailResult" class="ms-2"></span>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" id="saveEmailSettingsBtn">
                <i class="fas fa-save me-1"></i> Simpan Pengaturan Email
            </button>
        </div>
    </form>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning">
    <strong>⚠️ Akses Ditolak:</strong> Anda tidak memiliki akses ke pengaturan email.
</div>
<?php endif; // End of superadmin check for email configuration ?>

<hr class="my-4">

<!-- Section: Backup (Backup Settings) -->
<?php
// Get backup settings from controller (with defaults)
$backupSettings = $backupSettings ?? [];
$smbEnabled = !empty($backupSettings['smb_enabled']);
$ftpEnabled = !empty($backupSettings['ftp_enabled']);
?>

<?php if ($userRole === 'superadmin'): ?>
<div class="row mb-4">
    <div class="col-12">
        <h2>💾 Pengaturan Backup</h2>
        <p class="text-muted">Konfigurasi backup ke SMB (Windows Share) dan FTP server.</p>
        <div class="alert alert-info">
            <strong>🔒 Superadmin Only:</strong> Konfigurasi backup hanya dapat diakses oleh superadmin.
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="/settings/backup" id="backupSettingsForm">
            <?= Csrf::field(); ?>
            <input type="hidden" name="backup_type" id="backup_type" value="<?= htmlspecialchars($backupSettings['backup_type'] ?? 'local') ?>">
            <input type="hidden" name="backup_enabled" id="backup_enabled" value="<?= !empty($backupSettings['backup_enabled']) ? '1' : '0' ?>">
            <input type="hidden" name="retention_days" value="<?= (int)($backupSettings['retention_days'] ?? 30) ?>">
            
            <!-- SMB Settings -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fab fa-windows me-2"></i>SMB (Windows Share)
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="smb_enabled" name="smb_enabled" value="1" <?= $smbEnabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="smb_enabled">Aktifkan SMB</label>
                    </div>
                </div>
                <hr>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Host / IP Address</label>
                        <input type="text" class="form-control" name="smb_host" value="<?= htmlspecialchars($backupSettings['smb_host'] ?? '') ?>" placeholder="192.168.1.100">
                        <small class="text-muted">IP address atau hostname server SMB</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Share Name</label>
                        <input type="text" class="form-control" name="smb_share" value="<?= htmlspecialchars($backupSettings['smb_share'] ?? '') ?>" placeholder="backup">
                        <small class="text-muted">Nama folder share (tanpa leading slash)</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="smb_username" value="<?= htmlspecialchars($backupSettings['smb_username'] ?? '') ?>" placeholder="username">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="smb_password" value="<?= htmlspecialchars($backupSettings['smb_password_decrypted'] ?? '') ?>" placeholder="********">
                        <small class="text-muted">Kosongkan untuk tetap menggunakan password lama</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Workgroup (Opsional)</label>
                        <input type="text" class="form-control" name="smb_workgroup" value="<?= htmlspecialchars($backupSettings['smb_workgroup'] ?? '') ?>" placeholder="WORKGROUP">
                        <small class="text-muted">Workgroup/Domain Windows (jika ada)</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remote Path</label>
                        <input type="text" class="form-control" name="smb_path" value="<?= htmlspecialchars($backupSettings['smb_path'] ?? '/kombi-backups') ?>" placeholder="/kombi-backups">
                        <small class="text-muted">Path tujuan di dalam share</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMB Binary Path</label>
                        <input type="text" class="form-control" name="smb_binary" value="<?= htmlspecialchars($backupSettings['smb_binary'] ?? '/usr/bin/smbclient') ?>" placeholder="/usr/bin/smbclient">
                        <small class="text-muted">Path ke smbclient binary</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="testSmbBtn">
                        <i class="fas fa-plug me-1"></i> Test Koneksi SMB
                    </button>
                    <span id="testSmbResult" class="ms-2"></span>
                </div>
            </div>
            
            <!-- FTP Settings -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>FTP
                    </h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="ftp_enabled" name="ftp_enabled" value="1" <?= $ftpEnabled ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ftp_enabled">Aktifkan FTP</label>
                    </div>
                </div>
                <hr>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Host / IP Address</label>
                        <input type="text" class="form-control" name="ftp_host" value="<?= htmlspecialchars($backupSettings['ftp_host'] ?? '') ?>" placeholder="192.168.1.100">
                        <small class="text-muted">IP address atau hostname server FTP</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-control" name="ftp_port" value="<?= htmlspecialchars($backupSettings['ftp_port'] ?? 21) ?>" placeholder="21">
                        <small class="text-muted">Port FTP (default: 21)</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="ftp_username" value="<?= htmlspecialchars($backupSettings['ftp_username'] ?? '') ?>" placeholder="username">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="ftp_password" value="<?= htmlspecialchars($backupSettings['ftp_password_decrypted'] ?? '') ?>" placeholder="********">
                        <small class="text-muted">Kosongkan untuk tetap menggunakan password lama</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remote Directory</label>
                        <input type="text" class="form-control" name="ftp_path" value="<?= htmlspecialchars($backupSettings['ftp_path'] ?? '/kombi-backups') ?>" placeholder="/kombi-backups">
                        <small class="text-muted">Path tujuan di server FTP</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mode</label>
                        <select class="form-select" name="ftp_passive">
                            <option value="1" <?= (!empty($backupSettings['ftp_passive'])) ? 'selected' : '' ?>>Passive Mode</option>
                            <option value="0" <?= (empty($backupSettings['ftp_passive'])) ? 'selected' : '' ?>>Active Mode</option>
                        </select>
                        <small class="text-muted">Mode koneksi FTP</small>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="testFtpBtn">
                        <i class="fas fa-plug me-1"></i> Test Koneksi FTP
                    </button>
                    <span id="testFtpResult" class="ms-2"></span>
                </div>
            </div>
            
            <div class="alert alert-info">
                <strong>ℹ️ Info:</strong>
                <ul class="mb-0 mt-2">
                    <li>Backup akan otomatis disinkronkan ke SMB/FTP yang aktif setelah backup dibuat</li>
                    <li>Untuk SMB, pastikan server memiliki <code>smbclient</code> terinstall</li>
                    <li>Untuk FTP, pastikan ekstensi PHP FTP aktif</li>
                </ul>
            </div>
        </form>
    </div>
    
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="saveBackupSettingsBtn">
            <i class="fas fa-save me-1"></i> Simpan Pengaturan Backup
        </button>
    </div>
</div>

<?php else: ?>
<div class="alert alert-warning">
    <strong>⚠️ Akses Ditolak:</strong> Anda tidak memiliki akses ke pengaturan backup.
</div>
<?php endif; // End of superadmin check for backup settings ?>

<!-- Global Save All Button -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body text-center">
                <button type="button" class="btn btn-primary btn-lg" id="saveAllSettingsBtn">
                    <i class="fas fa-save me-1"></i> Simpan Semua Pengaturan
                </button>
                <a href="/dashboard" class="btn btn-secondary btn-lg ms-2">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testEmailBtn = document.getElementById('testEmailBtn');
    const testEmailResult = document.getElementById('testEmailResult');
    
    // Handle individual "Save App Settings" button
    const saveAppSettingsBtn = document.getElementById('saveAppSettingsBtn');
    if (saveAppSettingsBtn) {
        saveAppSettingsBtn.addEventListener('click', function() {
            const form = document.getElementById('settingsForm');
            const sectionInput = document.getElementById('sectionInput');
            sectionInput.value = 'app_name'; // Set section to trigger app settings save
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch('/settings/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        // Show success notification
                        showNotification('success', 'Pengaturan Aplikasi berhasil disimpan!');
                        // Reload page after short delay
                        setTimeout(() => window.location.href = '/settings', 1000);
                    } else {
                        showNotification('danger', data.message || 'Gagal menyimpan pengaturan.');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showNotification('danger', 'Terjadi kesalahan saat menyimpan.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('danger', 'Gagal menyimpan: ' + error.message);
            });
        });
    }
    
    // Handle individual "Save General Settings" button
    const saveGeneralSettingsBtn = document.getElementById('saveGeneralSettingsBtn');
    if (saveGeneralSettingsBtn) {
        saveGeneralSettingsBtn.addEventListener('click', function() {
            const form = document.getElementById('settingsForm2');
            const sectionInput = document.getElementById('sectionInput2');
            sectionInput.value = 'all'; // Save all settings in this form (active_angkatan + score_visibility + sla_threshold + progress_sla)
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch('/settings/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showNotification('success', 'Pengaturan Umum berhasil disimpan!');
                        setTimeout(() => window.location.href = '/settings', 1000);
                    } else {
                        showNotification('danger', data.message || 'Gagal menyimpan pengaturan.');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showNotification('danger', 'Terjadi kesalahan saat menyimpan.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('danger', 'Gagal menyimpan: ' + error.message);
            });
        });
    }
    
    // Handle individual "Save Email Settings" button
    const saveEmailSettingsBtn = document.getElementById('saveEmailSettingsBtn');
    if (saveEmailSettingsBtn) {
        saveEmailSettingsBtn.addEventListener('click', function() {
            const form = document.getElementById('settingsForm3');
            const sectionInput = document.getElementById('sectionInput3');
            sectionInput.value = 'email_config'; // Set section to trigger email settings save
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch('/settings/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showNotification('success', 'Pengaturan Email berhasil disimpan!');
                        setTimeout(() => window.location.href = '/settings', 1000);
                    } else {
                        showNotification('danger', data.message || 'Gagal menyimpan pengaturan.');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showNotification('danger', 'Terjadi kesalahan saat menyimpan.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('danger', 'Gagal menyimpan: ' + error.message);
            });
        });
    }
    
    // Handle individual "Save Workload Settings" button
    const saveWorkloadSettingsBtn = document.getElementById('saveWorkloadSettingsBtn');
    if (saveWorkloadSettingsBtn) {
        saveWorkloadSettingsBtn.addEventListener('click', function() {
            const form = document.getElementById('settingsFormWorkload');
            const sectionInput = document.getElementById('sectionInputWorkload');
            sectionInput.value = 'workload'; // Set section to trigger workload settings save
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch('/settings/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showNotification('success', 'Pengaturan Beban Dosen berhasil disimpan!');
                        setTimeout(() => window.location.href = '/settings', 1000);
                    } else {
                        showNotification('danger', data.message || 'Gagal menyimpan pengaturan.');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showNotification('danger', 'Terjadi kesalahan saat menyimpan.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('danger', 'Gagal menyimpan: ' + error.message);
            });
        });
    }
    
    // Handle individual "Save Evaluation Settings" button
    const saveEvaluationSettingsBtn = document.getElementById('saveEvaluationSettingsBtn');
    if (saveEvaluationSettingsBtn) {
        saveEvaluationSettingsBtn.addEventListener('click', function() {
            const form = document.getElementById('settingsFormEvaluation');
            const sectionInput = document.getElementById('sectionInputEvaluation');
            sectionInput.value = 'evaluation_config'; // Set section to trigger evaluation settings save
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch('/settings/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showNotification('success', 'Pengaturan Edit Nilai berhasil disimpan!');
                        setTimeout(() => window.location.href = '/settings', 1000);
                    } else {
                        showNotification('danger', data.message || 'Gagal menyimpan pengaturan.');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showNotification('danger', 'Terjadi kesalahan saat menyimpan.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showNotification('danger', 'Gagal menyimpan: ' + error.message);
            });
        });
    }
    
    // Helper function to show notifications
    function showNotification(type, message) {
        // Remove existing notifications
        const existingAlert = document.querySelector('.settings-notification');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Map type to icon
        const iconMap = {
            'success': 'check-circle',
            'danger': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        const icon = iconMap[type] || 'bell';
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} settings-notification`;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.style.minWidth = '300px';
        alert.innerHTML = `<i class="fas fa-${icon} me-2"></i>${message}`;
        
        document.body.appendChild(alert);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    if (testEmailBtn) {
        testEmailBtn.addEventListener('click', function() {
            testEmailBtn.disabled = true;
            testEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mengirim...';
            testEmailResult.innerHTML = '';
            
            // Get CSRF token from the form
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
            if (!csrfToken) {
                testEmailResult.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>CSRF token tidak ditemukan</span>';
                testEmailBtn.disabled = false;
                testEmailBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Kirim Email Test';
                return;
            }
            
            // Create FormData to send CSRF token
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            
            fetch('/settings/test-email', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                console.log('Response status:', response.status);
                console.log('Response text:', text);
                
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        testEmailResult.innerHTML = '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Email test berhasil dikirim ke ' + data.to + '</span>';
                    } else {
                        testEmailResult.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>' + data.message + '</span>';
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    testEmailResult.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Error: ' + (text.substring(0, 100) || 'Unknown error') + '</span>';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                testEmailResult.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Gagal mengirim email test: ' + error.message + '</span>';
            })
            .finally(() => {
                testEmailBtn.disabled = false;
                testEmailBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Kirim Email Test';
            });
        });
    }
    
    // Handle global "Save All Settings" button
    const saveAllSettingsBtn = document.getElementById('saveAllSettingsBtn');
    saveAllSettingsBtn?.addEventListener('click', function() {
        // Submit all forms in sequence with proper section values
        const settingsForm = document.getElementById('settingsForm');
        const settingsForm2 = document.getElementById('settingsForm2');
        const settingsForm3 = document.getElementById('settingsForm3');
        const settingsFormWorkload = document.getElementById('settingsFormWorkload');
        const settingsFormEvaluation = document.getElementById('settingsFormEvaluation');
        
        const forms = [
            { form: settingsForm, section: 'app_name' },
            { form: settingsForm2, section: 'all' },
            { form: settingsForm3, section: 'email_config' },
            { form: settingsFormWorkload, section: 'workload' },
            { form: settingsFormEvaluation, section: 'evaluation_config' }
        ].filter(f => f.form !== null);
        
        let completedForms = 0;
        const totalForms = forms.length;
        let hasError = false;
        
        // Show loading notification
        showNotification('info', 'Menyimpan semua pengaturan...');
        
        forms.forEach(({ form, section }) => {
            // Set the section input value before submitting
            const sectionInput = form.querySelector('input[name="section"]');
            if (sectionInput) {
                sectionInput.value = section;
            }
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Submit via fetch
            fetch('/settings/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (!data.success) {
                        hasError = true;
                        console.error('Form save error:', data.message);
                    }
                } catch (e) {
                    // Non-JSON response might indicate an error
                    hasError = true;
                    console.error('Parse error:', e);
                }
                
                completedForms++;
                if (completedForms === totalForms) {
                    // All forms submitted
                    if (hasError) {
                        showNotification('warning', 'Sebagian pengaturan berhasil disimpan dengan beberapa error.');
                    } else {
                        showNotification('success', 'Semua pengaturan berhasil disimpan!');
                    }
                    // Reload page after short delay
                    setTimeout(() => window.location.href = '/settings', 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hasError = true;
                completedForms++;
                if (completedForms === totalForms) {
                    showNotification('danger', 'Terjadi kesalahan saat menyimpan beberapa pengaturan.');
                    setTimeout(() => window.location.href = '/settings', 2000);
                }
            });
        });
    });
    
    // Handle Backup Settings buttons
    const testSmbBtn = document.getElementById('testSmbBtn');
    const testFtpBtn = document.getElementById('testFtpBtn');
    const saveBackupSettingsBtn = document.getElementById('saveBackupSettingsBtn');
    
    // Test SMB Connection
    testSmbBtn?.addEventListener('click', function() {
        const result = document.getElementById('testSmbResult');
        result.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Testing...</span>';
        
        const formData = new FormData();
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        formData.append('host', document.querySelector('[name="smb_host"]').value);
        formData.append('share', document.querySelector('[name="smb_share"]').value);
        formData.append('username', document.querySelector('[name="smb_username"]').value);
        formData.append('password', document.querySelector('[name="smb_password"]').value);
        formData.append('workgroup', document.querySelector('[name="smb_workgroup"]').value);
        formData.append('binary', document.querySelector('[name="smb_binary"]').value);
        formData.append('path', document.querySelector('[name="smb_path"]').value);
        formData.append('smb_enabled', document.querySelector('[name="smb_enabled"]').checked ? '1' : '0');
        
        fetch('/settings/backup/test-smb', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    result.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
                } else {
                    result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + data.message + '</span>';
                }
            } catch (e) {
                result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Invalid response</span>';
            }
        })
        .catch(error => {
            result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + error.message + '</span>';
        });
    });
    
    // Test FTP Connection
    testFtpBtn?.addEventListener('click', function() {
        const result = document.getElementById('testFtpResult');
        result.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Testing...</span>';
        
        const formData = new FormData();
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        formData.append('host', document.querySelector('[name="ftp_host"]').value);
        formData.append('port', document.querySelector('[name="ftp_port"]').value);
        formData.append('username', document.querySelector('[name="ftp_username"]').value);
        formData.append('password', document.querySelector('[name="ftp_password"]').value);
        formData.append('path', document.querySelector('[name="ftp_path"]').value);
        formData.append('passive', document.querySelector('[name="ftp_passive"]').value);
        formData.append('ftp_enabled', document.querySelector('[name="ftp_enabled"]').checked ? '1' : '0');
        
        fetch('/settings/backup/test-ftp', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    result.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
                } else {
                    result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + data.message + '</span>';
                }
            } catch (e) {
                result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Invalid response</span>';
            }
        })
        .catch(error => {
            result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + error.message + '</span>';
        });
    });
    
    // Save Backup Settings
    saveBackupSettingsBtn?.addEventListener('click', function() {
        const form = document.getElementById('backupSettingsForm');
        const formData = new FormData(form);
        
        // Determine backup_type based on enabled checkboxes
        const smbEnabled = document.querySelector('[name="smb_enabled"]')?.checked;
        const ftpEnabled = document.querySelector('[name="ftp_enabled"]')?.checked;
        
        if (smbEnabled) {
            formData.set('backup_type', 'smb');
            formData.set('backup_enabled', '1');
        } else if (ftpEnabled) {
            formData.set('backup_type', 'ftp');
            formData.set('backup_enabled', '1');
        } else {
            formData.set('backup_type', 'local');
            formData.set('backup_enabled', '0');
        }
        
        fetch('/settings/backup', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    showNotification('success', 'Pengaturan Backup berhasil disimpan!');
                    setTimeout(() => window.location.href = '/settings', 1000);
                } else {
                    showNotification('danger', data.message || 'Gagal menyimpan pengaturan.');
                }
            } catch (e) {
                showNotification('danger', 'Terjadi kesalahan saat menyimpan.');
            }
        })
        .catch(error => {
            showNotification('danger', 'Gagal menyimpan: ' + error.message);
        });
    });
});
</script>

<!-- Email Diagnostic Modal -->
<div class="modal fade" id="emailDiagnosticModal" tabindex="-1" aria-labelledby="emailDiagnosticModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailDiagnosticModalLabel">
                    <i class="fas fa-stethoscope me-2"></i>Diagnosa Konfigurasi Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="diagnosticLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Menjalankan diagnosa...</p>
                </div>
                <div id="diagnosticResults" style="display: none;">
                    <!-- Overall Status -->
                    <div id="diagnosticStatus" class="alert mb-3"></div>
                    
                    <!-- Configuration Info -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong><i class="fas fa-cog me-2"></i>Konfigurasi Saat Ini</strong>
                        </div>
                        <div class="card-body" id="diagnosticConfig"></div>
                    </div>
                    
                    <!-- Check Results -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong><i class="fas fa-tasks me-2"></i>Hasil Pengecekan</strong>
                        </div>
                        <div class="card-body" id="diagnosticChecks"></div>
                    </div>
                    
                    <!-- Recommendations -->
                    <div class="card" id="recommendationsCard" style="display: none;">
                        <div class="card-header">
                            <strong><i class="fas fa-lightbulb me-2"></i>Rekomendasi</strong>
                        </div>
                        <div class="card-body" id="diagnosticRecommendations"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="runDiagnosticBtn">
                    <i class="fas fa-play me-1"></i> Jalankan Diagnosa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const diagnoseEmailBtn = document.getElementById('diagnoseEmailBtn');
    const runDiagnosticBtn = document.getElementById('runDiagnosticBtn');
    const diagnosticLoading = document.getElementById('diagnosticLoading');
    const diagnosticResults = document.getElementById('diagnosticResults');
    const diagnosticStatus = document.getElementById('diagnosticStatus');
    const diagnosticConfig = document.getElementById('diagnosticConfig');
    const diagnosticChecks = document.getElementById('diagnosticChecks');
    const diagnosticRecommendations = document.getElementById('diagnosticRecommendations');
    const recommendationsCard = document.getElementById('recommendationsCard');
    
    // Run diagnostic when modal opens
    diagnoseEmailBtn?.addEventListener('click', function() {
        // Reset modal state
        diagnosticLoading.style.display = 'block';
        diagnosticResults.style.display = 'none';
    });
    
    // Listen for modal shown event, then run diagnostic
    const modalElement = document.getElementById('emailDiagnosticModal');
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function() {
            runDiagnostic();
        });
    }
    
    runDiagnosticBtn?.addEventListener('click', function() {
        runDiagnostic();
    });
    
    function runDiagnostic() {
        diagnosticLoading.style.display = 'block';
        diagnosticResults.style.display = 'none';
        runDiagnosticBtn.disabled = true;
        
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (!csrfToken) {
            showError('CSRF token tidak ditemukan');
            return;
        }
        
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        
        fetch('/settings/diagnose-email', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            console.log('Diagnostic response:', text);
            try {
                const result = JSON.parse(text);
                if (result.success) {
                    displayDiagnosticResults(result.data);
                } else {
                    let errorMsg = result.message || 'Gagal menjalankan diagnosa';
                    if (result.file) {
                        errorMsg += ' (File: ' + result.file + ':' + result.line + ')';
                    }
                    showError(errorMsg);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                showError('Error: ' + (text.substring(0, 500) || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showError('Gagal menjalankan diagnosa: ' + error.message);
        })
        .finally(() => {
            diagnosticLoading.style.display = 'none';
            runDiagnosticBtn.disabled = false;
        });
    }
    
    function displayDiagnosticResults(data) {
        diagnosticResults.style.display = 'block';
        
        // Overall status
        const statusClass = data.status === 'pass' ? 'alert-success' : 'alert-warning';
        const statusIcon = data.status === 'pass' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        const statusText = data.status === 'pass'
            ? '<strong><i class="fas ' + statusIcon + ' me-2"></i>Semua Pengecekan Berhasil!</strong> Konfigurasi email Anda sudah benar.'
            : '<strong><i class="fas ' + statusIcon + ' me-2"></i>Masalah Ditemukan!</strong> Silakan periksa rekomendasi di bawah.';
        diagnosticStatus.className = 'alert ' + statusClass;
        diagnosticStatus.innerHTML = statusText;
        
        // Configuration
        let configHtml = '<div class="row">';
        configHtml += '<div class="col-md-6"><small class="text-muted">Status:</small> <span class="badge ' + (data.config.enabled ? 'bg-success' : 'bg-secondary') + '">' + (data.config.enabled ? 'Aktif' : 'Non-aktif') + '</span></div>';
        configHtml += '<div class="col-md-6"><small class="text-muted">Host:</small> ' + escapeHtml(data.config.host) + '</div>';
        configHtml += '<div class="col-md-6"><small class="text-muted">Port:</small> ' + data.config.port + '</div>';
        configHtml += '<div class="col-md-6"><small class="text-muted">Enkripsi:</small> ' + data.config.encryption.toUpperCase() + '</div>';
        configHtml += '<div class="col-md-6"><small class="text-muted">Username:</small> ' + (data.config.username_set ? '✓ Diatur' : '✗ Belum diatur') + '</div>';
        configHtml += '<div class="col-md-6"><small class="text-muted">Password:</small> ' + (data.config.password_set ? '✓ Diatur' : '✗ Belum diatur') + '</div>';
        configHtml += '</div>';
        diagnosticConfig.innerHTML = configHtml;
        
        // Checks
        let checksHtml = '';
        const checkIcons = {
            'pass': '<i class="fas fa-check-circle text-success"></i>',
            'fail': '<i class="fas fa-times-circle text-danger"></i>',
            'skip': '<i class="fas fa-minus-circle text-warning"></i>'
        };
        
        for (const [key, check] of Object.entries(data.checks)) {
            const icon = checkIcons[check.status] || checkIcons['skip'];
            const statusClass = check.status === 'pass' ? 'list-group-item-success' : (check.status === 'fail' ? 'list-group-item-danger' : 'list-group-item-warning');
            
            checksHtml += '<div class="list-group-item ' + statusClass + '">';
            checksHtml += '<div class="d-flex justify-content-between align-items-start">';
            checksHtml += '<div><strong>' + icon + ' ' + check.name + '</strong></div>';
            checksHtml += '<span class="badge ' + (check.status === 'pass' ? 'bg-success' : (check.status === 'fail' ? 'bg-danger' : 'bg-warning')) + '">' + check.status.toUpperCase() + '</span>';
            checksHtml += '</div>';
            
            if (check.error) {
                checksHtml += '<div class="text-danger small mt-1"><i class="fas fa-exclamation-triangle me-1"></i>' + escapeHtml(check.error) + '</div>';
            }
            
            if (check.details && Object.keys(check.details).length > 0) {
                checksHtml += '<div class="mt-2 small">';
                for (const [detailKey, detailValue] of Object.entries(check.details)) {
                    if (Array.isArray(detailValue)) {
                        checksHtml += '<div><strong>' + detailKey + ':</strong> <ul class="mb-0 ps-3">';
                        detailValue.forEach(v => checksHtml += '<li>' + escapeHtml(v) + '</li>');
                        checksHtml += '</ul></div>';
                    } else {
                        checksHtml += '<div><strong>' + detailKey + ':</strong> ' + escapeHtml(String(detailValue)) + '</div>';
                    }
                }
                checksHtml += '</div>';
            }
            
            checksHtml += '</div>';
        }
        diagnosticChecks.innerHTML = '<div class="list-group">' + checksHtml + '</div>';
        
        // Recommendations
        if (data.recommendations && data.recommendations.length > 0) {
            recommendationsCard.style.display = 'block';
            let recHtml = '';
            
            const priorityClasses = {
                'critical': 'danger',
                'high': 'warning',
                'medium': 'info',
                'low': 'secondary'
            };
            
            data.recommendations.forEach(rec => {
                const priorityClass = priorityClasses[rec.priority] || 'secondary';
                recHtml += '<div class="alert alert-' + priorityClass + ' mb-2">';
                recHtml += '<strong><i class="fas fa-exclamation-circle me-1"></i>' + escapeHtml(rec.title) + '</strong>';
                recHtml += '<p class="mb-2 small">' + escapeHtml(rec.description) + '</p>';
                if (rec.commands && rec.commands.length > 0) {
                    recHtml += '<div class="mt-2">';
                    recHtml += '<small class="text-muted">Commands:</small>';
                    recHtml += '<div class="bg-dark text-light p-2 rounded mt-1" style="font-family: monospace; font-size: 0.85rem;">';
                    rec.commands.forEach(cmd => {
                        recHtml += '<div class="mb-1">' + escapeHtml(cmd) + '</div>';
                    });
                    recHtml += '</div></div>';
                }
                recHtml += '</div>';
            });
            
            diagnosticRecommendations.innerHTML = recHtml;
        } else {
            recommendationsCard.style.display = 'none';
        }
    }
    
    function showError(message) {
        diagnosticLoading.style.display = 'none';
        diagnosticResults.style.display = 'block';
        diagnosticStatus.className = 'alert alert-danger';
        diagnosticStatus.innerHTML = '<strong><i class="fas fa-times-circle me-2"></i>Error</strong> ' + escapeHtml(message);
        diagnosticConfig.innerHTML = '';
        diagnosticChecks.innerHTML = '';
        diagnosticRecommendations.innerHTML = '';
        recommendationsCard.style.display = 'none';
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
