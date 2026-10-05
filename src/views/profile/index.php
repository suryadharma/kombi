<?php
$user = $user ?? [];
$role = $role ?? '';
$details = $details ?? null;

$roleLabels = [
    'superadmin' => 'Superadmin',
    'kombi' => 'Koordinator/Kombi',
    'dosen' => 'Dosen',
    'dosen_pembimbing' => 'Dosen Pembimbing',
    'dosen_penguji' => 'Dosen Penguji',
    'mahasiswa' => 'Mahasiswa',
    'penguji_eksternal' => 'Penguji Eksternal'
];

$titleStatusBadge = [
    'MENUNGGU' => 'bg-info',
    'DITERIMA' => 'bg-success',
    'DITOLAK' => 'bg-danger',
    'PERLU_REVISI' => 'bg-warning text-dark'
];
?>

<div class="row">
    <div class="col-12">
        <h2>Profil Pengguna</h2>
        <p class="text-muted mb-0">Informasi akun dan data penting sesuai peran Anda.</p>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-circle"></i> Akun</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>
                
                <form method="POST" action="/profile/update" id="emailUpdateForm">
                    <?= Csrf::field(); ?>
                    <dl class="row mb-3">
                        <dt class="col-sm-5">Nama</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($user['name'] ?? '-') ?></dd>

                        <dt class="col-sm-5">Username</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($user['username'] ?? '-') ?></dd>

                        <dt class="col-sm-5">Peran</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?>
                            </span>
                        </dd>

                        <?php if (!empty($user['nim'])): ?>
                        <dt class="col-sm-5">NIM</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($user['nim']) ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($user['nip'])): ?>
                        <dt class="col-sm-5">NIP</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($user['nip']) ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-5">Email</dt>
                        <dd class="col-sm-7">
                            <div class="input-group input-group-sm">
                                <input type="email"
                                       class="form-control"
                                       name="email"
                                       id="userEmail"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       placeholder="email@contoh.com"
                                       required>
                                <button type="submit" class="btn btn-outline-primary" title="Simpan Email">
                                    <i class="fas fa-save"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Email digunakan untuk notifikasi (misal: hasil penilaian PDF untuk dosen)
                            </small>
                        </dd>

                        <dt class="col-sm-5">Dibuat</dt>
                        <dd class="col-sm-7">
                            <?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?>
                        </dd>
                    </dl>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6>Keamanan Akun</h6>
                <p class="text-muted small mb-2">Ganti password secara berkala untuk menjaga keamanan akun Anda.</p>
                <a href="/auth/change-password" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-key"></i> Ubah Password
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if ($role === 'mahasiswa'): ?>
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-graduate"></i> Detail Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <?php if ($details): ?>
                        <dl class="row">
                            <dt class="col-sm-4">Nama</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['name']) ?></dd>

                            <dt class="col-sm-4">NIM</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['nim']) ?></dd>

                            <dt class="col-sm-4">Angkatan</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['angkatan']) ?></dd>

                            <dt class="col-sm-4">Semester Masuk</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['semester_masuk']) ?></dd>

                            <?php if (!empty($details['semester_lulus'])): ?>
                            <dt class="col-sm-4">Semester Lulus</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['semester_lulus']) ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">Status Mahasiswa</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($details['status']) ?>
                                </span>
                            </dd>

                            <dt class="col-sm-4">Judul Skripsi</dt>
                            <dd class="col-sm-8">
                                <?php if (!empty($details['title'])): ?>
                                    <?= htmlspecialchars($details['title']) ?><br>
                                    <?php if (!empty($details['title_status'])): ?>
                                        <?php $badge = $titleStatusBadge[$details['title_status']] ?? 'bg-secondary'; ?>
                                        <span class="badge <?= $badge ?> mt-1">
                                            Status: <?= htmlspecialchars($details['title_status']) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Belum mengajukan judul</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            Data mahasiswa tidak ditemukan. Silakan hubungi Kombi untuk memastikan akun Anda terhubung dengan data mahasiswa.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($role === 'dosen' || $role === 'dosen_pembimbing' || $role === 'dosen_penguji'): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Detail Dosen</h5>
                </div>
                <div class="card-body">
                    <?php if ($details): ?>
                        <dl class="row">
                            <dt class="col-sm-4">Nama</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['name']) ?></dd>

                            <dt class="col-sm-4">NIP</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['nip']) ?></dd>

                            <dt class="col-sm-4">Program Studi</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($details['prodi'] ?? 'Teknologi Industri Pertanian') ?></dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge <?= !empty($details['is_external']) ? 'bg-warning text-dark' : 'bg-primary' ?>">
                                    <?= !empty($details['is_external']) ? 'Dosen Eksternal' : 'Dosen Internal' ?>
                                </span>
                            </dd>
                        </dl>

                        <?php if (!empty($details['assignments'])): ?>
                        <h6 class="mt-4">Penugasan Aktif</h6>
                        <ul class="list-unstyled">
                            <?php foreach ($details['assignments'] as $assignment): ?>
                                <li>
                                    <span class="badge bg-light text-dark">
                                        <?= htmlspecialchars(str_replace('_', ' ', ucfirst($assignment['role']))) ?>
                                    </span>
                                    &mdash; <?= htmlspecialchars($assignment['total']) ?> mahasiswa
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            Data dosen belum tersedia. Hubungi Kombi untuk menghubungkan akun Anda.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">Tidak ada detail tambahan untuk peran ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
