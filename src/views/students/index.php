<?php
$students = $students ?? [];
$role = $role ?? '';
$search = $search ?? '';
$angkatan = $angkatan ?? '';
$status = $status ?? 'AKTIF';
$useActiveAngkatan = $useActiveAngkatan ?? true;
$success = $success ?? null;
$assignmentsByStudent = $assignmentsByStudent ?? [];

if (array_key_exists('active_angkatan', $_GET)) {
    $useActiveAngkatan = (bool) $_GET['active_angkatan'];
}
?>

<style>
/* Memperbaiki tampilan dropdown "Show entries" */
.dataTables_length select {
    width: auto !important;
    min-width: 80px;
    padding-right: 30px !important;
}
</style>
<div class="row">
    <div class="col-12">
        <h2>Daftar Mahasiswa</h2>
    </div>
</div>

<?php
// Dapatkan daftar angkatan aktif dari pengaturan
$activeAngkatan = Settings::getActiveAngkatan();
$angkatanList = [];
foreach ($students as $student) {
    $studentAngkatan = $student['angkatan'] ?? null;
    if ($studentAngkatan === null || $studentAngkatan === '') {
        continue;
    }
    if ($useActiveAngkatan) {
        if (!empty($activeAngkatan) && !in_array($studentAngkatan, $activeAngkatan, true)) {
            continue;
        }
    }
    if (!in_array($studentAngkatan, $angkatanList, true)) {
        $angkatanList[] = $studentAngkatan;
    }
}
sort($angkatanList);
$angkatanOptions = [
    ['value' => '', 'label' => 'Semua Angkatan' . ($useActiveAngkatan ? ' Aktif' : '')]
];
foreach ($angkatanList as $angk) {
    $angkatanOptions[] = ['value' => (string)$angk, 'label' => (string)$angk];
}
$statusOptions = [
    ['value' => '', 'label' => 'Semua Status'],
    ['value' => 'AKTIF', 'label' => 'Aktif'],
    ['value' => 'CUTI', 'label' => 'Cuti'],
    ['value' => 'NON-AKTIF', 'label' => 'Non-Aktif'],
    ['value' => 'MENGULANG', 'label' => 'Mengulang'],
    ['value' => 'LULUS', 'label' => 'Lulus'],
];
ob_start(); ?>
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" role="switch" id="activeAngkatanSwitch" <?= $useActiveAngkatan ? 'checked' : '' ?>>
    <label class="form-check-label" for="activeAngkatanSwitch">
        Gunakan filter angkatan aktif
        <?php if (!empty($activeAngkatan)): ?>
            (<?= implode(', ', array_map('htmlspecialchars', $activeAngkatan)) ?>)
        <?php else: ?>
            (tidak ada angkatan aktif)
        <?php endif; ?>
    </label>
</div>
<?php
$filterExtra = ob_get_clean();
$filterConfig = [
    'id' => 'studentFilterForm',
    'method' => 'GET',
    'action' => '/students',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'angkatan',
            'label' => 'Filter Angkatan',
            'value' => (string)($angkatan ?? ''),
            'options' => $angkatanOptions,
            'col' => 3
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'label' => 'Filter Status',
            'value' => (string)($status ?? ''),
            'options' => $statusOptions,
            'col' => 3
        ],
        [
            'type' => 'search',
            'name' => 'search',
            'label' => 'Cari Mahasiswa',
            'placeholder' => 'NIM atau nama...',
            'value' => $search ?? '',
            'col' => 4
        ],
        [
            'type' => 'hidden',
            'name' => 'active_angkatan',
            'value' => $useActiveAngkatan ? '1' : '0'
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Filter', 'icon' => 'fas fa-search', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/students'],
    ],
    'extra' => $filterExtra
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row mb-3 align-items-center">
    <div class="col-lg-8 col-md-7">
        <h2 class="mb-1"><i class="fas fa-user-graduate"></i> Data Mahasiswa</h2>
        <p class="text-muted mb-0">Kelola data mahasiswa, impor dari file Excel, atau tambahkan secara manual.</p>
        <p class="text-muted small mb-0">Catatan: Secara default hanya menampilkan mahasiswa dengan status selain "LULUS". Gunakan filter status untuk melihat semua mahasiswa.</p>
    </div>
    <div class="col-lg-4 col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#guideModal">
            <i class="fas fa-book"></i> Panduan
        </button>
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-excel"></i> Import Excel
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fas fa-plus"></i> Tambah
        </button>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="bulkDeleteForm" method="POST" action="/students/bulk-delete">
                    <?= Csrf::field(); ?>
                    <?php $studentsCount = count($students); ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Hasil Filter</h5>
                        <span class="badge bg-info text-dark"><?= $studentsCount ?> mahasiswa</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="studentsTable">
                            <thead>
                                <tr>
                                    <th style="width: 5%"><input type="checkbox" data-role="select-all-students"></th>
                                    <th style="width: 15%">NIM</th>
                                    <th style="width: 25%">Nama</th>
                                    <th style="width: 10%">Angkatan</th>
                                    <th style="width: 15%">Semester Masuk</th>
                                    <th style="width: 15%">Status</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                <tr>
                                    <td><input type="checkbox" disabled></td>
                                    <td><span class="text-muted">-</span></td>
                                    <td><span class="text-muted">-</span></td>
                                    <td><span class="text-muted">-</span></td>
                                    <td><span class="text-muted">-</span></td>
                                    <td><span class="text-muted">-</span></td>
                                    <td class="text-center" colspan="2">Tidak ada data mahasiswa</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><input type="checkbox" name="selected_students[]" value="<?= htmlspecialchars($student['id']) ?>"></td>
                                    <td><?= htmlspecialchars($student['nim']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['angkatan']) ?></td>
                                    <td><?= htmlspecialchars($student['semester_masuk']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        switch ($student['status']) {
                                            case 'AKTIF':
                                                $statusClass = 'badge bg-success';
                                                break;
                                            case 'CUTI':
                                                $statusClass = 'badge bg-warning text-dark';
                                                break;
                                            case 'NON-AKTIF':
                                                $statusClass = 'badge bg-secondary';
                                                break;
                                            case 'MENGULANG':
                                                $statusClass = 'badge bg-danger';
                                                break;
                                            case 'LULUS':
                                                $statusClass = 'badge bg-primary';
                                                break;
                                            default:
                                                $statusClass = 'badge bg-light text-dark';
                                        }
                                        ?>
                                        <span class="<?= $statusClass ?>"><?= htmlspecialchars($student['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="/students/<?= $student['id'] ?>" class="btn btn-sm btn-info">Lihat</a>
                                        <a href="/students/<?= $student['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                        <?php if (!empty($student['user_id'])): ?>
                                        <a href="/users/reset-password?type=mahasiswa&amp;user_id=<?= (int)$student['user_id'] ?>"
                                           class="btn btn-sm btn-outline-warning"
                                           title="Reset Password Mahasiswa">
                                            <i class="fas fa-key"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="/students/<?= $student['id'] ?>/delete" class="btn btn-sm btn-danger">Hapus</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-danger mt-3" id="bulkDeleteBtn" disabled>Hapus yang Dipilih</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/students/import/process" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="student_file" class="form-label">Pilih File CSV</label>
                        <input type="file" class="form-control" id="student_file" name="student_file" accept=".csv" required>
                        <div class="form-text">File harus dalam format CSV dengan kolom: NIM,Nama,Angkatan,Semester Masuk,Status (Status bersifat opsional)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Impor Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/students">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Tambah Mahasiswa Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" class="form-control" id="nim" name="nim" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="angkatan" class="form-label">Angkatan</label>
                        <input type="number" class="form-control" id="angkatan" name="angkatan" min="1900" max="2100" required>
                    </div>
                    <div class="mb-3">
                        <label for="semester_masuk" class="form-label">Semester Masuk</label>
                        <input type="text" class="form-control" id="semester_masuk" name="semester_masuk" placeholder="Contoh: 23241" required>
                        <div class="form-text">Format: TTTT untuk tahun ajaran, 1 untuk ganjil, 2 untuk genap. Contoh: 23241 untuk 2023/2024 Ganjil</div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="AKTIF">Aktif</option>
                            <option value="CUTI">Cuti</option>
                            <option value="NON-AKTIF">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tambah Mahasiswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Panduan Penggunaan -->
<div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="guideModalLabel">
                    <i class="fas fa-book"></i> Panduan Penggunaan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="guideTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usage" type="button" role="tab">
                            <i class="fas fa-filter"></i> Cara Menggunakan Filter
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab">
                            <i class="fas fa-file-excel"></i> Panduan Impor
                        </button>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="guideTabContent">
                    <div class="tab-pane fade show active" id="usage" role="tabpanel">
                        <h6>Cara Menggunakan Filter</h6>
                        <ol>
                            <li><strong>Filter Angkatan</strong>: Memfilter mahasiswa berdasarkan tahun masuk
                                <ul>
                                    <li>Secara default hanya menampilkan angkatan aktif yang telah diatur di pengaturan sistem</li>
                                    <li>Gunakan switch "Gunakan filter angkatan aktif" untuk mengaktifkan/menonaktifkan filter ini</li>
                                </ul>
                            </li>
                            <li><strong>Filter Status</strong>: Memfilter mahasiswa berdasarkan status akademik:
                                <ul>
                                    <li><strong>Aktif</strong>: Mahasiswa yang sedang menempuh perkuliahan (default)</li>
                                    <li><strong>Cuti</strong>: Mahasiswa yang sedang cuti</li>
                                    <li><strong>Non-Aktif</strong>: Mahasiswa yang tidak aktif</li>
                                    <li><strong>Lulus</strong>: Mahasiswa yang telah menyelesaikan studi</li>
                                </ul>
                            </li>
                            <li><strong>Pencarian</strong>: Mencari mahasiswa berdasarkan NIM atau nama</li>
                        </ol>
                        
                        <h6>Informasi Tambahan</h6>
                        <ul>
                            <li>Secara default, sistem hanya menampilkan mahasiswa dengan status <strong>Aktif</strong> dari angkatan aktif</li>
                            <li>Switch "Gunakan filter angkatan aktif" memungkinkan Anda untuk melihat semua mahasiswa atau hanya dari angkatan aktif</li>
                            <li>Jika switch aktif (berwarna biru), hanya menampilkan mahasiswa dari angkatan aktif</li>
                            <li>Jika switch tidak aktif (abu-abu), menampilkan semua mahasiswa dari semua angkatan</li>
                            <li>Filter dapat dikombinasikan untuk mendapatkan hasil yang lebih spesifik</li>
                            <li>Angkatan aktif dapat diatur di menu <a href="/settings">Pengaturan</a></li>
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="import" role="tabpanel">
                        <h6>Cara Mengimpor Data Mahasiswa dari File CSV</h6>
                        <ol>
                            <li>Klik tombol <strong>"Import Excel"</strong> di atas</li>
                            <li>Siapkan file CSV dengan format berikut:
                                <pre class="bg-light p-2 rounded">
NIM,Nama,Angkatan,Semester Masuk,Status
23241001,Andi Susanto,2023,23241,AKTIF
23241002,Budi Prasetyo,2023,23241,AKTIF
23241003,Citra Dewi,2023,23241,CUTI</pre>
                            </li>
                            <li>Kolom yang wajib diisi: NIM, Nama, Angkatan, Semester Masuk</li>
                            <li>Kolom Status bersifat opsional, default adalah "AKTIF"</li>
                            <li>Format Semester Masuk:
                                <ul>
                                    <li><strong>23241</strong> untuk tahun ajaran 2023/2024 semester ganjil</li>
                                    <li><strong>23242</strong> untuk tahun ajaran 2023/2024 semester genap</li>
                                    <li><strong>2324</strong> untuk tahun ajaran 2023/2024 semester ganjil (format alternatif)</li>
                                </ul>
                            </li>
                            <li>Pilih file dan klik "Impor Data"</li>
                        </ol>
                        
                        <h6>Informasi Akun Mahasiswa</h6>
                        <ul>
                            <li>Username: NIM mahasiswa</li>
                            <li>Password default: Sama dengan NIM</li>
                            <li>Mahasiswa harus mengganti password saat login pertama kali</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('studentFilterForm');
    const activeAngkatanSwitch = document.getElementById('activeAngkatanSwitch');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const masterSelector = 'input[data-role="select-all-students"]';
    const rowSelector = 'input[name="selected_students[]"]';
    const $studentsTable = $('#studentsTable');
    let dataTableApi = null;

    if (filterForm && activeAngkatanSwitch) {
        activeAngkatanSwitch.addEventListener('change', function () {
            let activeAngkatanInput = filterForm.querySelector('input[name="active_angkatan"]');
            if (!activeAngkatanInput) {
                activeAngkatanInput = document.createElement('input');
                activeAngkatanInput.type = 'hidden';
                activeAngkatanInput.name = 'active_angkatan';
                filterForm.appendChild(activeAngkatanInput);
            }
            activeAngkatanInput.value = this.checked ? '1' : '0';
            filterForm.submit();
        });
    }

    if (typeof $ !== 'undefined' && $.fn.DataTable && $studentsTable.length) {
        dataTableApi = $studentsTable.DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            pageLength: 25,
            order: [[1, 'asc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            columnDefs: [
                { width: '5%', targets: 0 },
                { width: '15%', targets: [1, 3, 4, 5] },
                { width: '25%', targets: 2 },
                { width: '15%', targets: 6 },
                { orderable: false, searchable: false, targets: [0, 6] }
            ],
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
    }

    const getRowCheckboxes = () => {
        if (dataTableApi) {
            const nodesApi = dataTableApi.rows({ page: 'current' }).nodes();
            const nodeArray = (nodesApi && typeof nodesApi.toArray === 'function')
                ? nodesApi.toArray()
                : nodesApi;
            return $(nodeArray).find(rowSelector);
        }
        return $(rowSelector);
    };

    const updateBulkDeleteButton = () => {
        if (!bulkDeleteBtn) {
            return;
        }
        const anyChecked = $(rowSelector + ':checked').length > 0;
        bulkDeleteBtn.disabled = !anyChecked;
    };

    const syncMasterCheckboxes = () => {
        const $rowCheckboxes = getRowCheckboxes();
        const $masterCheckboxes = $(masterSelector);

        if ($rowCheckboxes.length === 0) {
            $masterCheckboxes.prop('checked', false).prop('indeterminate', false);
            return;
        }

        const total = $rowCheckboxes.length;
        const checkedCount = $rowCheckboxes.filter(':checked').length;

        $masterCheckboxes
            .prop('checked', checkedCount === total)
            .prop('indeterminate', checkedCount > 0 && checkedCount < total);
    };

    $(document).on('change', masterSelector, function () {
        const shouldCheck = $(this).is(':checked');
        $(masterSelector).prop('checked', shouldCheck).prop('indeterminate', false);
        getRowCheckboxes().each(function () {
            this.checked = shouldCheck;
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });
        updateBulkDeleteButton();
        syncMasterCheckboxes();
    });

    $(document).on('change', rowSelector, function () {
        updateBulkDeleteButton();
        syncMasterCheckboxes();
    });

    if (dataTableApi) {
        $studentsTable.on('draw.dt', function () {
            syncMasterCheckboxes();
            updateBulkDeleteButton();
        });
    }

    updateBulkDeleteButton();
    syncMasterCheckboxes();
});
<?php
$studentsPageInlineScript = ob_get_clean();
if (!isset($inlineScripts)) {
    $inlineScripts = [];
}
$inlineScripts[] = $studentsPageInlineScript;
?>
