<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?><?= htmlspecialchars(AppSettings::getName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/public/images/favicon.ico">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <link href="/assets/css/custom.css?v=20250210" rel="stylesheet">
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link href="<?= $style ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
    if (!function_exists('kombi_nav_is_active')) {
        function kombi_nav_is_active(array $patterns): bool
        {
            $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Remove query string if present
            $currentUri = strtok($currentUri, '?');
            
            foreach ($patterns as $pattern) {
                // Exact match
                if ($pattern === $currentUri) {
                    return true;
                }
                
                // Root path special case
                if ($pattern === '/' && $currentUri === '/') {
                    return true;
                }
                
                // For patterns ending with *, do prefix match
                if (str_ends_with($pattern, '*')) {
                    $prefix = rtrim($pattern, '*');
                    if (str_starts_with($currentUri, $prefix)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
    ?>
    <style>
        .dropdown-menu-admin {
            min-width: 320px;
            border-radius: 1rem;
            border: 1px solid rgba(20, 184, 166, 0.4);
            box-shadow: 0 22px 44px rgba(15, 23, 42, 0.35);
            background: #0f172a;
            color: #f8fafc;
        }

        .dropdown-menu-admin .menu-link {
            display: flex;
            gap: 0.75rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.75rem;
            color: #f8fafc !important;
        }

        .dropdown-menu-admin .menu-link .fw-semibold {
            color: #f8fafc;
        }

        .dropdown-menu-admin .menu-link small {
            color: rgba(255, 255, 255, 0.97) !important;
            font-size: 0.82rem;
            display: block;
        }

        .dropdown-grid .dropdown-toggle::after {
            margin-left: 0.35rem;
        }

        .dropdown-menu-admin .menu-link:hover {
            background-color: rgba(79, 70, 229, 0.35);
            color: #ffffff;
        }

        .dropdown-menu-admin .menu-link.active-link {
            background-color: rgba(20, 184, 166, 0.35);
            color: #ffffff;
        }

        .dropdown-menu-admin .menu-icon {
            width: 34px;
            height: 34px;
            border-radius: 0.75rem;
            background: rgba(20, 184, 166, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #14b8a6;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<?php if (isset($_SESSION['user_id'])): ?>
    <?php
        $rawRole = $_SESSION['active_role'] ?? ($_SESSION['role'] ?? null);
        $activeRole = $rawRole !== null ? trim((string)$rawRole) : null;
        $currentRole = $activeRole ? strtolower($activeRole) : null;
        $adminMenuGroups = [];
        if (in_array($currentRole, ['kombi', 'superadmin'], true)) {
            $adminMenuGroups = [
                [
                    'title' => 'Data Master',
                    'icon' => 'fas fa-layer-group',
                    'patterns' => ['/students', '/lecturers', '/titles', '/components'],
                    'items' => [
                        [
                            'label' => 'Mahasiswa',
                            'desc' => 'Kelola mahasiswa & monitoring',
                            'icon' => 'fas fa-user-graduate',
                            'url' => '/students',
                        ],
                        [
                            'label' => 'Dosen',
                            'desc' => 'Daftar & impor dosen',
                            'icon' => 'fas fa-chalkboard-teacher',
                            'url' => '/lecturers',
                        ],
                        [
                            'label' => 'Judul Skripsi',
                            'desc' => 'Verifikasi & daftar judul',
                            'icon' => 'fas fa-file-alt',
                            'url' => '/titles/view',
                        ],
                        [
                            'label' => 'Komponen Penilaian',
                            'desc' => 'Bobot & indikator',
                            'icon' => 'fas fa-sliders-h',
                            'url' => '/components',
                        ],
                    ],
                ],
                [
                    'title' => 'Penugasan & Jadwal',
                    'icon' => 'fas fa-clipboard-check',
                    'patterns' => ['/assignments', '/events', '/external'],
                    'items' => [
                        [
                            'label' => 'Penetapan Dosen',
                            'desc' => 'Pembimbing & penguji',
                            'icon' => 'fas fa-user-tie',
                            'url' => '/assignments/set',
                        ],
                        [
                            'label' => 'Log Perubahan Dosen',
                            'desc' => 'Monitoring perubahan dosen',
                            'icon' => 'fas fa-history',
                            'url' => '/assignments/history',
                        ],
                        [
                            'label' => 'Penjadwalan',
                            'desc' => 'Kelola jadwal sidang',
                            'icon' => 'fas fa-calendar-alt',
                            'url' => '/events',
                        ],
                        [
                            'label' => 'Penguji Eksternal',
                            'desc' => 'Token & portal',
                            'icon' => 'fas fa-id-badge',
                            'url' => '/external/invite',
                        ],
                    ],
                ],
                [
                    'title' => 'Penilaian',
                    'icon' => 'fas fa-star',
                    'patterns' => ['/scores*'],
                    'items' => [
                        [
                            'label' => 'Input Nilai',
                            'desc' => 'Panel penilaian',
                            'icon' => 'fas fa-edit',
                            'url' => '/scores/submit',
                        ],
                        [
                            'label' => 'Rekap Nilai',
                            'desc' => 'Rekap nilai mahasiswa lulus',
                            'icon' => 'fas fa-clipboard-list',
                            'url' => '/scores/recap',
                        ],
                    ],
                ],
                [
                    'title' => 'Laporan & Monitoring',
                    'icon' => 'fas fa-chart-pie',
                    'patterns' => ['/reports/*', '/students/progress'],
                    'items' => [
                        [
                            'label' => 'Masa Studi',
                            'desc' => 'Periode masa studi mahasiswa',
                            'icon' => 'fas fa-calendar-alt',
                            'url' => '/reports',
                        ],
                        [
                            'label' => 'Pelacakan Tugas Akhir',
                            'desc' => 'Sempro sampai ujian',
                            'icon' => 'fas fa-route',
                            'url' => '/reports/tracking',
                        ],
                        [
                            'label' => 'Data Lulusan',
                            'desc' => 'Nilai akhir skripsi',
                            'icon' => 'fas fa-graduation-cap',
                            'url' => '/reports/graduated',
                        ],
                        [
                            'label' => 'Beban Dosen',
                            'desc' => 'Rasio pembimbing/penguji',
                            'icon' => 'fas fa-chart-line',
                            'url' => '/reports/workload',
                        ],
                        [
                            'label' => 'Beban Dosen Historis',
                            'desc' => 'Laporan beban dosen historis',
                            'icon' => 'fas fa-history',
                            'url' => '/reports/historical-workload',
                        ],
                        [
                            'label' => 'SLA Entri Nilai',
                            'desc' => 'Durasi entri nilai',
                            'icon' => 'fas fa-clock',
                            'url' => '/reports/sla',
                            'visible' => ($currentRole === 'superadmin'),
                        ],
                        [
                            'label' => 'Riwayat Bypass',
                            'desc' => 'Riwayat bypass nilai',
                            'icon' => 'fas fa-forward',
                            'url' => '/reports/bypass',
                        ],
                    ],
                ],
                [
                    'title' => 'Utility',
                    'icon' => 'fas fa-toolbox',
                    'patterns' => ['/users', '/users/roles', '/backups', '/settings'],
                    'items' => [
                        [
                            'label' => 'Pengguna & Peran',
                            'desc' => 'Kelola akun & akses',
                            'icon' => 'fas fa-users-cog',
                            'url' => '/users/roles',
                            'visible' => ($currentRole === 'superadmin'),
                        ],
                        [
                            'label' => 'Backup & Restore',
                            'desc' => 'Cadangan database',
                            'icon' => 'fas fa-database',
                            'url' => '/backups',
                            'visible' => ($currentRole === 'superadmin'),
                        ],
                        [
                            'label' => 'Pengaturan',
                            'desc' => 'Konfigurasi aplikasi',
                            'icon' => 'fas fa-cog',
                            'url' => '/settings',
                        ],
                    ],
                ],
            ];
        }
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-leaf"></i> <?= htmlspecialchars(AppSettings::getName()) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (!empty($adminMenuGroups)): ?>
                        <?php foreach ($adminMenuGroups as $group): ?>
                            <?php
                                $visibleItems = array_values(array_filter($group['items'], function ($item) {
                                    return $item['visible'] ?? true;
                                }));
                                if (empty($visibleItems)) {
                                    continue;
                                }
                                $groupActive = kombi_nav_is_active($group['patterns']);
                            ?>
                            <li class="nav-item dropdown dropdown-grid">
                                <a class="nav-link dropdown-toggle <?= $groupActive ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">
                                    <i class="<?= htmlspecialchars($group['icon']) ?>"></i> <?= htmlspecialchars($group['title']) ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-admin p-3">
                                    <?php foreach ($visibleItems as $item): ?>
                                        <?php $itemActive = kombi_nav_is_active([$item['url']]); ?>
                                        <a class="dropdown-item menu-link <?= $itemActive ? 'active-link' : '' ?>"
                                            href="<?= htmlspecialchars($item['url']) ?>">
                                            <span class="menu-icon"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                                            <span>
                                                <span class="fw-semibold"><?= htmlspecialchars($item['label']) ?></span>
                                                <?php if (!empty($item['desc'])): ?>
                                                    <small class="d-block text-muted"><?= htmlspecialchars($item['desc']) ?></small>
                                                <?php endif; ?>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php elseif (in_array($currentRole, ['dosen_pembimbing', 'dosen_penguji', 'dosen'], true)): ?>
                    <!-- Menu untuk Dosen -->
                    <li class="nav-item">
                        <a class="nav-link" href="/scores/submit"><i class="fas fa-star"></i> Penilaian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/scores/lecturer-recap"><i class="fas fa-clipboard-list"></i> Rekap Nilai</a>
                    </li>
                    <?php elseif ($currentRole === 'mahasiswa'): ?>
                    <!-- Menu untuk Mahasiswa -->
                    <li class="nav-item">
                        <a class="nav-link" href="/titles/submit"><i class="fas fa-paper-plane"></i> Ajukan Judul</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/titles/view"><i class="fas fa-eye"></i> Lihat Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/timeline"><i class="fas fa-road"></i> Timeline</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <?php
                            $availableRoles = $_SESSION['available_roles'] ?? [];
                            $roleDisplayNames = [
                                'superadmin' => 'Superadmin',
                                'kombi' => 'Kombi',
                                'dosen' => 'Dosen',
                                'dosen_pembimbing' => 'Dosen',
                                'dosen_penguji' => 'Dosen',
                                'mahasiswa' => 'Mahasiswa',
                                'penguji_eksternal' => 'Penguji Eksternal'
                            ];
                            $currentPath = $_SERVER['REQUEST_URI'] ?? '/dashboard';
                        ?>
                        <ul class="dropdown-menu dropdown-menu-end modern-navbar-dropdown">
                            <?php if ($activeRole): ?>
                            <li class="dropdown-header text-muted">Peran Aktif</li>
                            <li class="px-3 mb-2">
                                <span class="badge rounded-pill modern-role-badge">
                                    <?= htmlspecialchars($roleDisplayNames[$currentRole] ?? ucwords(str_replace('_', ' ', $activeRole))) ?>
                                </span>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($availableRoles) && count($availableRoles) > 1): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-header text-muted">Ganti Peran</li>
                                <?php foreach ($availableRoles as $roleOption): ?>
                                    <?php
                                        $roleOptionValue = trim((string)$roleOption);
                                        $roleOptionKey = strtolower($roleOptionValue);
                                        if ($activeRole && $roleOptionKey === $currentRole) {
                                            continue;
                                        }
                                    ?>
                                    <li>
                                        <form action="/auth/switch-role" method="POST" class="px-3 py-1">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="role" value="<?= htmlspecialchars($roleOptionValue) ?>">
                                            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentPath) ?>">
                                            <button type="submit" class="btn btn-link modern-role-switch">
                                                <?= htmlspecialchars($roleDisplayNames[$roleOptionKey] ?? ucwords(str_replace('_', ' ', $roleOptionValue))) ?>
                                            </button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <?php if ($currentRole === 'mahasiswa'): ?>
                            <li><a class="dropdown-item" href="/profile"><i class="fas fa-id-card"></i> Profil</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/auth/change-password"><i class="fas fa-key"></i> Ubah Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="container mt-4 flex-grow-1">
        <?php if (isset($content)): ?>
            <?= $content ?>
        <?php else: ?>
            <?= $viewContent ?? '' ?>
        <?php endif; ?>
    </div>
    
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0"><?= AppSettings::getCopyrightText() ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"><?= AppSettings::getDeveloperLink() ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSRF Auto-Refresh -->
    <script src="/public/js/csrf-auto-refresh.js"></script>
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
