<?php
$waitingTitles = $waitingTitles ?? [];
$otherTitles = $otherTitles ?? [];
$success = $success ?? null;
$error = $error ?? null;
$activeRole = strtolower($_SESSION['active_role'] ?? ($_SESSION['role'] ?? ''));
$canManage = in_array($activeRole, ['kombi', 'superadmin'], true);
$canVerify = $canManage;
$angkatanList = $angkatanList ?? [];
$currentAngkatan = $currentAngkatan ?? '';
$currentSearch = $currentSearch ?? '';
?>

<style>
/* Custom styles for titles view page */
.titles-page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0.5rem;
}

.titles-page-header h2 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
}

.titles-page-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

/* Section cards */
.titles-section-card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}

.titles-section-card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.titles-section-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.titles-section-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.1rem;
}

.titles-section-header small {
    font-size: 0.8rem;
    opacity: 0.75;
}

/* Waiting section */
.titles-waiting-section {
    border-left: 4px solid #ffc107;
}

.titles-waiting-section .titles-section-header {
    background: linear-gradient(135deg, #fff3cd 0%, #ffe8a1 100%);
}

.titles-waiting-section .titles-section-header h5 {
    color: #856404;
}

/* Verified section */
.titles-verified-section .titles-section-header {
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
}

.titles-verified-section .titles-section-header h5 {
    color: #0c5460;
}

/* Tables */
.titles-table {
    margin-bottom: 0;
}

.titles-table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #495057;
    padding: 0.875rem 1rem;
    vertical-align: middle;
}

.titles-table tbody td {
    padding: 1rem;
    vertical-align: middle;
    font-size: 0.9rem;
}

.titles-table tbody tr {
    transition: background-color 0.2s ease;
}

.titles-table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Waiting table rows */
.titles-table-waiting tbody tr {
    background-color: #fff9e6;
}

.titles-table-waiting tbody tr:hover {
    background-color: #fff3cd;
}

/* Status badges */
.titles-status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.35em 0.65em;
    border-radius: 0.375rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Action buttons */
.titles-action-btn {
    font-size: 0.8rem;
    padding: 0.4rem 0.75rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.titles-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Empty state */
.titles-empty-state {
    padding: 3rem 2rem;
    text-align: center;
}

.titles-empty-state i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}

.titles-empty-state p {
    font-size: 1rem;
    color: #6c757d;
    margin: 0;
}

/* Student name with badge */
.titles-student-name {
    font-weight: 500;
    color: #212529;
}

.titles-student-badge {
    display: inline-block;
    margin-top: 0.25rem;
}

/* Title preview */
.titles-title-preview {
    color: #495057;
    line-height: 1.4;
    max-width: 300px;
}

/* Date display */
.titles-date {
    font-size: 0.85rem;
    color: #6c757d;
    white-space: nowrap;
}

/* Verified by */
.titles-verified-by {
    font-size: 0.85rem;
    color: #6c757d;
}

/* NIM display */
.titles-nim {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85rem;
}

/* Filter section */
.titles-filter-section {
    background-color: #f8f9fa;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .titles-page-header {
        padding: 1.5rem 1rem;
    }
    
    .titles-page-header h2 {
        font-size: 1.5rem;
    }
    
    .titles-table thead th,
    .titles-table tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .titles-title-preview {
        max-width: 200px;
    }
}
</style>

<!-- Page Header -->
<div class="titles-page-header mb-4">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-book-open me-2"></i>Daftar Judul Skripsi</h2>
                <p>Kelola dan verifikasi judul skripsi mahasiswa</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-inline-flex gap-3">
                    <div class="text-center">
                        <div class="display-6 fw-bold text-white"><?= count($waitingTitles) ?></div>
                        <small class="opacity-75">Menunggu</small>
                    </div>
                    <div class="text-center">
                        <div class="display-6 fw-bold text-white"><?= count($otherTitles) ?></div>
                        <small class="opacity-75">Terverifikasi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Flash messages
if ($success): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- SECTION 1: Menunggu Verifikasi -->
<?php if (!empty($waitingTitles)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card titles-section-card titles-waiting-section">
            <div class="titles-section-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-bell text-warning me-2"></i>
                        Menunggu Verifikasi
                        <span class="badge bg-warning text-dark ms-2 titles-status-badge"><?= count($waitingTitles) ?></span>
                    </h5>
                    <small class="text-muted">Judul yang memerlukan verifikasi segera</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table titles-table titles-table-waiting mb-0" id="waitingTitlesTable">
                        <thead>
                            <tr>
                                <th style="width: 10%">NIM</th>
                                <th style="width: 18%">Nama Mahasiswa</th>
                                <th style="width: 8%">Angkatan</th>
                                <th style="width: 32%">Judul</th>
                                <th style="width: 14%">Tanggal</th>
                                <th style="width: 18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($waitingTitles as $title): ?>
                            <tr>
                                <td>
                                    <span class="titles-nim"><?= htmlspecialchars($title['nim']) ?></span>
                                </td>
                                <td>
                                    <div class="titles-student-name"><?= htmlspecialchars($title['student_name']) ?></div>
                                    <?php
                                        $studentStatus = isset($title['student_status']) ? strtoupper(trim((string)$title['student_status'])) : '';
                                        if ($studentStatus === 'LULUS'):
                                    ?>
                                        <span class="badge bg-success titles-student-badge">Lulus</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($title['angkatan']) ?></span>
                                </td>
                                <td>
                                    <div class="titles-title-preview">
                                        <?php
                                            $titleText = (string)$title['title'];
                                            if (function_exists('mb_substr')) {
                                                $titlePreview = mb_substr($titleText, 0, 70);
                                                $isTrimmed = mb_strlen($titleText) > 70;
                                            } else {
                                                $titlePreview = substr($titleText, 0, 70);
                                                $isTrimmed = strlen($titleText) > 70;
                                            }
                                            echo htmlspecialchars($isTrimmed ? $titlePreview . '…' : $titlePreview);
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="titles-date">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('d M Y', strtotime($title['submitted_at'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($title['submitted_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/titles/view/<?= $title['id'] ?>" class="btn btn-sm btn-outline-info titles-action-btn">
                                            <i class="fas fa-eye me-1"></i>Lihat
                                        </a>
                                        <?php if ($canVerify): ?>
                                            <a href="/titles/verify/<?= $title['id'] ?>" class="btn btn-sm btn-warning titles-action-btn">
                                                <i class="fas fa-check me-1"></i>Verifikasi
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card titles-section-card border-success">
            <div class="titles-empty-state">
                <i class="fas fa-check-circle text-success"></i>
                <p class="mb-0 text-success fw-medium">Tidak ada judul yang menunggu verifikasi</p>
                <small class="text-muted">Semua judul telah diproses</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SECTION 2: Semua Judul Terverifikasi -->
<div class="row">
    <div class="col-12">
        <div class="card titles-section-card titles-verified-section">
            <div class="titles-section-header">
                <h5 class="mb-1">
                    <i class="fas fa-book text-primary me-2"></i>
                    Semua Judul Terverifikasi
                    <span class="badge bg-primary ms-2 titles-status-badge"><?= count($otherTitles) ?></span>
                </h5>
                <small class="text-muted">Daftar judul yang telah diverifikasi</small>
            </div>
            <div class="card-body">
                <!-- Filter -->
                <?php
                $angkatanOptions = [
                    ['value' => '', 'label' => 'Semua ' . (!empty($angkatanList) ? 'Angkatan Aktif' : 'Angkatan')]
                ];
                foreach ($angkatanList as $angkatan) {
                    $angkatanOptions[] = [
                        'value' => (string)$angkatan,
                        'label' => (string)$angkatan
                    ];
                }
                $filterConfig = [
                    'id' => 'titlesFilterForm',
                    'method' => 'GET',
                    'action' => '/titles/view',
                    'fields' => [
                        [
                            'type' => 'select',
                            'name' => 'angkatan',
                            'label' => 'Filter Angkatan',
                            'value' => (string)$currentAngkatan,
                            'options' => $angkatanOptions,
                            'col' => 3
                        ],
                        [
                            'type' => 'search',
                            'name' => 'search',
                            'label' => 'Cari Mahasiswa',
                            'placeholder' => 'NIM atau nama...',
                            'value' => $currentSearch ?? '',
                            'col' => 4
                        ],
                    ],
                    'actions' => [
                        ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
                        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/titles/view'],
                    ]
                ];
                include VIEW_PATH . '/components/filter_bar.php';
                ?>
                
                <div class="table-responsive mt-3">
                    <table class="table titles-table mb-0" id="otherTitlesTable">
                        <thead>
                            <tr>
                                <th style="width: 9%">NIM</th>
                                <th style="width: 16%">Nama</th>
                                <th style="width: 7%">Angkatan</th>
                                <th style="width: 24%">Judul</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 12%">Tanggal</th>
                                <th style="width: 12%">Diverifikasi</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($otherTitles)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="titles-empty-state py-4">
                                        <i class="fas fa-inbox text-muted"></i>
                                        <p class="mb-0 text-muted">Tidak ada data judul</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($otherTitles as $title): ?>
                            <tr>
                                <td>
                                    <span class="titles-nim"><?= htmlspecialchars($title['nim']) ?></span>
                                </td>
                                <td>
                                    <div class="titles-student-name"><?= htmlspecialchars($title['student_name']) ?></div>
                                    <?php
                                        $studentStatus = isset($title['student_status']) ? strtoupper(trim((string)$title['student_status'])) : '';
                                        if ($studentStatus === 'LULUS'):
                                    ?>
                                        <span class="badge bg-success titles-student-badge">Lulus</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($title['angkatan']) ?></span>
                                </td>
                                <td>
                                    <div class="titles-title-preview">
                                        <?php
                                            $titleText = (string)$title['title'];
                                            if (function_exists('mb_substr')) {
                                                $titlePreview = mb_substr($titleText, 0, 55);
                                                $isTrimmed = mb_strlen($titleText) > 55;
                                            } else {
                                                $titlePreview = substr($titleText, 0, 55);
                                                $isTrimmed = strlen($titleText) > 55;
                                            }
                                            echo htmlspecialchars($isTrimmed ? $titlePreview . '…' : $titlePreview);
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    switch ($title['status']) {
                                        case 'DITERIMA':
                                            $statusClass = 'bg-success';
                                            break;
                                        case 'DITOLAK':
                                            $statusClass = 'bg-danger';
                                            break;
                                        case 'PERLU_REVISI':
                                            $statusClass = 'bg-info';
                                            break;
                                    }
                                    ?>
                                    <span class="badge titles-status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($title['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="titles-date">
                                        <i class="far fa-calendar me-1"></i>
                                        <?= date('d M Y', strtotime($title['submitted_at'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($title['submitted_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="titles-verified-by">
                                        <?php if (!empty($title['verified_by_name'])): ?>
                                            <i class="fas fa-user-check me-1"></i>
                                            <?= htmlspecialchars($title['verified_by_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/titles/view/<?= $title['id'] ?>" class="btn btn-sm btn-info titles-action-btn">
                                            <i class="fas fa-eye me-1"></i>Lihat
                                        </a>
                                        <?php if ($canManage): ?>
                                            <a href="/titles/<?= $title['id'] ?>/edit" class="btn btn-sm btn-primary titles-action-btn">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined') {
        // Nonaktifkan peringatan DataTables
        $.fn.dataTable.ext.errMode = 'none';
        
        // DataTables for waiting titles
        if ($('#waitingTitlesTable').length) {
            var waitingTable = $('#waitingTitlesTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json",
                    emptyTable: "Tidak ada judul yang menunggu verifikasi",
                    search: "_INPUT_",
                    searchPlaceholder: "Cari..."
                },
                pageLength: 10,
                order: [[4, 'asc']],
                lengthMenu: [ [5, 10, 25, 50], [5, 10, 25, 50] ],
                columnDefs: [
                    { width: "10%", targets: 0 },
                    { width: "18%", targets: 1 },
                    { width: "8%", targets: 2 },
                    { width: "32%", targets: 3 },
                    { width: "14%", targets: 4 },
                    { width: "18%", targets: 5 }
                ],
                autoWidth: false,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                drawCallback: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });
            
            waitingTable.on('error.dt', function (e, settings, techNote, message) {
                console.log('DataTables error: ', message);
            });
        }
        
        // DataTables for other titles
        if ($('#otherTitlesTable').length) {
            var otherTable = $('#otherTitlesTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json",
                    search: "_INPUT_",
                    searchPlaceholder: "Cari..."
                },
                pageLength: 25,
                order: [[5, 'desc']],
                lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
                columnDefs: [
                    { width: "9%", targets: 0 },
                    { width: "16%", targets: 1 },
                    { width: "7%", targets: 2 },
                    { width: "24%", targets: 3 },
                    { width: "10%", targets: 4 },
                    { width: "12%", targets: 5 },
                    { width: "12%", targets: 6 },
                    { width: "10%", targets: 7 }
                ],
                autoWidth: false,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                drawCallback: function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                    $('.dataTables_length select').addClass('form-select form-select-sm');
                }
            });
            
            otherTable.on('error.dt', function (e, settings, techNote, message) {
                console.log('DataTables error: ', message);
            });
        }
    }
});
</script>
