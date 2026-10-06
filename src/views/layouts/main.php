<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $pageTitle = AppSettings::getFullName();
    if (isset($title)) {
        if (is_array($title)) {
            $firstValue = reset($title);
            $pageTitle = is_string($firstValue) ? $firstValue : AppSettings::getFullName();
        } elseif (is_string($title)) {
            $pageTitle = $title;
        }
    }
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
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars(AppSettings::getFaviconUrl()) ?>">
    <link rel="shortcut icon" href="<?= htmlspecialchars(AppSettings::getFaviconUrl()) ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link href="/assets/css/custom.css?v=20261006b" rel="stylesheet">

    <!-- jQuery (loaded early for inline scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .modern-navbar {
            background: linear-gradient(135deg, rgba(27, 39, 53, 0.94), rgba(47, 63, 82, 0.94));
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.2);
            border-bottom: 2px solid rgba(20, 184, 166, 0.45);
        }

        .modern-navbar .navbar-brand {
            font-weight: 700;
            color: #F8FAFC;
            letter-spacing: 0.6px;
            text-shadow: 0 2px 6px rgba(15, 23, 42, 0.45);
        }

        .modern-navbar .navbar-brand i {
            color: #14B8A6;
        }

        .modern-navbar .nav-link {
            color: rgba(241, 245, 249, 0.9);
            font-weight: 600;
            letter-spacing: 0.35px;
            text-transform: uppercase;
            border-radius: 0.6rem;
            transition: color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
            padding: 0.65rem 0.95rem;
            text-shadow: 0 2px 6px rgba(15, 23, 42, 0.55);
        }

        .modern-navbar .nav-link:hover,
        .modern-navbar .nav-link:focus {
            color: #FFFFFF;
            background-color: rgba(79, 70, 229, 0.32);
            box-shadow: inset 0 -3px 0 rgba(20, 184, 166, 0.55);
        }

        .modern-navbar .nav-link.active {
            color: #FFFFFF;
            background-color: rgba(79, 70, 229, 0.4);
            box-shadow: inset 0 -3px 0 #14B8A6;
        }

        .modern-navbar .dropdown-menu {
            background: rgba(22, 31, 43, 0.96);
            border-radius: 12px;
            border: 1px solid rgba(79, 70, 229, 0.35);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.35);
        }

        .modern-navbar .dropdown-item {
            color: rgba(226, 232, 240, 0.92);
            font-weight: 500;
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
        }

        .modern-navbar .dropdown-item:hover,
        .modern-navbar .dropdown-item:focus {
            background-color: rgba(79, 70, 229, 0.35);
            color: #FFFFFF;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }

        .sidebar {
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            height: calc(100vh - 56px);
            overflow-y: auto;
            position: sticky;
            top: 56px;
        }

        .main-content {
            padding-top: 20px;
        }

        .dataTables_length select {
            width: auto !important;
            min-width: 80px;
            padding-right: 30px !important;
        }

        .select2-container .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem + 2px);
        }

        .dropdown-grid .dropdown-toggle::after {
            margin-left: 0.35rem;
        }

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
            transition: background-color 0.2s ease, transform 0.2s ease;
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

        .dropdown-menu-admin .menu-link:hover {
            background-color: rgba(79, 70, 229, 0.35);
            transform: translateX(3px);
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

    <!-- Custom CSS from view if exists -->
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-xl navbar-dark modern-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="fas fa-graduation-cap"></i> KOMBI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <?php
                $rawActiveRole = $_SESSION['active_role'] ?? ($_SESSION['role'] ?? null);
                $activeRole = $rawActiveRole !== null ? trim((string)$rawActiveRole) : null;
                $currentRole = $activeRole !== null ? strtolower($activeRole) : null;
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
                                    'desc' => 'Verifikasi dan daftar judul',
                                    'icon' => 'fas fa-file-alt',
                                    'url' => '/titles/view',
                                ],
                                [
                                    'label' => 'Komponen Penilaian',
                                    'desc' => 'Bobot dan indikator',
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
                                    'desc' => 'Undangan & token',
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
                                    'desc' => 'Panel nilai dosen',
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
                            'patterns' => ['/reports/*', '/students/progress', '/timeline', '/students/assignment-status'],
                            'items' => [
                                [
                                    'label' => 'Masa Studi',
                                    'desc' => 'Periode masa studi mahasiswa',
                                    'icon' => 'fas fa-calendar-alt',
                                    'url' => '/reports',
                                ],
                                [
                                    'label' => 'Pelacakan Tugas Akhir',
                                    'desc' => 'Status Sempro-Sidang',
                                    'icon' => 'fas fa-route',
                                    'url' => '/reports/tracking',
                                ],
                                [
                                    'label' => 'Data Lulusan',
                                    'desc' => 'Detail nilai skripsi',
                                    'icon' => 'fas fa-graduation-cap',
                                    'url' => '/reports/graduated',
                                ],
                                [
                                    'label' => 'Beban Dosen',
                                    'desc' => 'Distribusi pembimbing/penguji',
                                    'icon' => 'fas fa-people-carry',
                                    'url' => '/reports/workload',
                                ],
                                [
                                    'label' => 'Beban Dosen Historis',
                                    'desc' => 'Laporan beban dosen historis',
                                    'icon' => 'fas fa-history',
                                    'url' => '/reports/historical-workload',
                                ],
                                [
                                    'label' => 'Status Penetapan Dosen',
                                    'desc' => 'Monitoring status penetapan dosen mahasiswa',
                                    'icon' => 'fas fa-tasks',
                                    'url' => '/students/assignment-status',
                                ],
                                [
                                    'label' => 'SLA Entri Nilai',
                                    'desc' => 'Analitik durasi',
                                    'icon' => 'fas fa-clock',
                                    'url' => '/reports/sla',
                                    'visible' => ($currentRole === 'superadmin'),
                                ],
                                [
                                    'label' => 'Riwayat Bypass',
                                    'desc' => 'Audit perubahan nilai',
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
                                    'desc' => 'Cadangan sistem',
                                    'icon' => 'fas fa-database',
                                    'url' => '/backups',
                                    'visible' => ($currentRole === 'superadmin'),
                                ],
                                [
                                    'label' => 'Pengaturan',
                                    'desc' => 'Kebijakan aplikasi',
                                    'icon' => 'fas fa-cog',
                                    'url' => '/settings',
                                ],
                            ],
                        ],
                    ];
                }
                ?>
               <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/dashboard') ? 'active' : '' ?>"
                                href="/dashboard">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>

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
                                    <a class="nav-link dropdown-toggle <?= $groupActive ? 'active' : '' ?>"
                                        href="#" role="button" data-bs-toggle="dropdown">
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
                        <?php endif; ?>

                        <?php if ($currentRole === 'mahasiswa'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/timeline') ? 'active' : '' ?>"
                                    href="/timeline">
                                    <i class="fas fa-stream"></i> Timeline Skripsi
                                </a>
                            </li>
                        <?php elseif (in_array($currentRole, ['dosen_pembimbing', 'dosen_penguji', 'dosen'], true)): ?>
                        <!-- Menu untuk Dosen -->
                        <li class="nav-item">
                            <a class="nav-link" href="/scores/submit"><i class="fas fa-star"></i> Penilaian</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/scores/lecturer-recap"><i class="fas fa-clipboard-list"></i> Rekap Nilai</a>
                        </li>
                        <?php elseif ($currentRole === 'mahasiswa'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_SERVER['REQUEST_URI'] === '/timeline') ? 'active' : '' ?>"
                                href="/timeline">
                                <i class="fas fa-stream"></i> Timeline Skripsi
                            </a>
                        </li>
                        <?php endif; ?>

                    </ul>

                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
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
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li class="dropdown-header text-muted">Ganti Peran</li>
                                    <?php foreach ($availableRoles as $roleOption): ?>
                                        <?php
                                        $normalizedOption = strtolower(trim((string)$roleOption));
                                        if ($activeRole && $normalizedOption === $currentRole) {
                                            continue;
                                        }
                                        ?>
                                        <li>
                                            <form action="/auth/switch-role" method="POST" class="px-3 py-1">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="role" value="<?= htmlspecialchars($roleOption) ?>">
                                                <input type="hidden" name="redirect_to"
                                                    value="<?= htmlspecialchars($currentPath) ?>">
                                                <button type="submit" class="btn btn-link modern-role-switch">
                                                    <?= htmlspecialchars($roleDisplayNames[$normalizedOption] ?? ucwords(str_replace('_', ' ', $roleOption))) ?>
                                                </button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-circle"></i> Profil</a>
                                </li>
                                <?php if (in_array($currentRole, ['kombi', 'superadmin'], true)): ?>
                                    <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog"></i> Pengaturan</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid flex-grow-1">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-12 ms-sm-auto main-content">
                <?php if (isset($content)): ?>
                    <?= $content ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <footer class="footer mt-auto text-center">
        <div class="container-fluid">
            <small>
                <i class="fas fa-copyright"></i> <?= date('Y') ?> <?= htmlspecialchars(AppSettings::getFooterText()) ?> | Developed by <?= AppSettings::getDeveloperLink() ?>
            </small>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- CSRF Auto-Refresh -->
    <script src="/public/js/csrf-auto-refresh.js"></script>

    <!-- DataTables Indonesian Translation -->
    <script>
        // Set default language for DataTables
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            }
        });
    </script>

    <!-- Custom JS from view if exists -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($inlineScripts)): ?>
        <?php foreach ($inlineScripts as $inlineScript): ?>
            <script>
                <?= $inlineScript ?>
            </script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>
