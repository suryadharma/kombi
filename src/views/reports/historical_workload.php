<?php
$workloads = $workloads ?? [];
$totals = $totals ?? ['dosen' => 0, 'total_bimbingan' => 0, 'total_penguji' => 0];
$yearList = $yearList ?? [];
$periodList = $periodList ?? [];
$selectedTahunMulai = $selectedTahunMulai ?? ((int)date('Y') - 5);
$selectedTahunSelesai = $selectedTahunSelesai ?? (int)date('Y');
$selectedFilterBy = $selectedFilterBy ?? 'angkatan';
$selectedRoleType = $selectedRoleType ?? 'all';
$currentSearch = $currentSearch ?? '';

// Helper function to sort period keys
$sortPeriod = static function (array $data): array {
    $sorted = $data;
    uksort($sorted, static function ($a, $b) {
        // Handle 'Aktif' and 'Tidak diketahui' specially
        if ($a === 'Aktif') return 1;
        if ($b === 'Aktif') return -1;
        if ($a === 'Tidak diketahui') return 1;
        if ($b === 'Tidak diketahui') return -1;
        
        // Numeric comparison for years
        $aNumeric = is_numeric($a);
        $bNumeric = is_numeric($b);
        if ($aNumeric && $bNumeric) {
            return (int)$a <=> (int)$b;
        }
        if ($aNumeric) {
            return -1;
        }
        if ($bNumeric) {
            return 1;
        }
        return strcmp($a, $b);
    });
    return $sorted;
};
?>

<div class="row">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Laporan Beban Dosen Historis</h2>
            <p class="text-muted mb-0">Laporan beban dosen untuk periode tertentu, termasuk mahasiswa aktif dan lulus.</p>
        </div>
    </div>
</div>

<?php
// Build year options based on actual data in database
$yearOptions = [];
$minYear = !empty($yearList) ? min($yearList) : (int)date('Y') - 10;
$maxYear = !empty($yearList) ? max($yearList) : (int)date('Y');
$currentYear = (int) date('Y');

// Add some buffer years
$startYear = $minYear;
$endYear = max($maxYear, $currentYear);

for ($year = $startYear; $year <= $endYear + 1; $year++) {
    $yearOptions[] = ['value' => (string) $year, 'label' => (string) $year];
}

$filterConfig = [
    'id' => 'historicalWorkloadFilterForm',
    'method' => 'GET',
    'action' => '/reports/historical-workload',
    'fields' => [
        [
            'type' => 'select',
            'name' => 'tahun_mulai',
            'label' => 'Tahun Mulai',
            'value' => (string) $selectedTahunMulai,
            'options' => $yearOptions,
            'col' => 2
        ],
        [
            'type' => 'select',
            'name' => 'tahun_selesai',
            'label' => 'Tahun Selesai',
            'value' => (string) $selectedTahunSelesai,
            'options' => $yearOptions,
            'col' => 2
        ],
        [
            'type' => 'select',
            'name' => 'filter_by',
            'label' => 'Filter Berdasarkan',
            'value' => $selectedFilterBy,
            'options' => [
                ['value' => 'tahun_lulus', 'label' => 'Tahun Lulus Mahasiswa'],
                ['value' => 'angkatan', 'label' => 'Angkatan Mahasiswa']
            ],
            'col' => 2
        ],
        [
            'type' => 'select',
            'name' => 'role_type',
            'label' => 'Tipe Peran',
            'value' => $selectedRoleType,
            'options' => [
                ['value' => 'all', 'label' => 'Semua Peran'],
                ['value' => 'pembimbing', 'label' => 'Hanya Pembimbing'],
                ['value' => 'penguji', 'label' => 'Hanya Penguji']
            ],
            'col' => 2
        ],
        [
            'type' => 'search',
            'name' => 'lecturer_search',
            'label' => 'Cari Dosen',
            'placeholder' => 'Nama dosen',
            'value' => $currentSearch,
            'col' => 3
        ],
    ],
    'actions' => [
        ['type' => 'submit', 'label' => 'Terapkan', 'icon' => 'fas fa-filter', 'variant' => 'primary'],
        ['type' => 'link', 'label' => 'Reset', 'icon' => 'fas fa-rotate-left', 'variant' => 'outline-secondary', 'url' => '/reports/historical-workload'],
    ]
];
include VIEW_PATH . '/components/filter_bar.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Jumlah Dosen</h6>
                <h3 class="mb-0"><?= htmlspecialchars($totals['dosen']) ?></h3>
                <small class="text-muted">Total dosen dalam periode <?= htmlspecialchars($selectedTahunMulai) ?> - <?= htmlspecialchars($selectedTahunSelesai) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Total Bimbingan</h6>
                <h3 class="mb-0"><?= htmlspecialchars($totals['total_bimbingan']) ?></h3>
                <small class="text-muted">Akumulasi mahasiswa yang dibimbing (aktif & lulus)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase mb-2">Total Pengujian</h6>
                <h3 class="mb-0"><?= htmlspecialchars($totals['total_penguji']) ?></h3>
                <small class="text-muted">Akumulasi mahasiswa yang diuji (aktif & lulus)</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Distribusi Beban Per Dosen</h5>
                <span class="badge bg-light text-dark">
                    Periode: <?= htmlspecialchars($selectedTahunMulai) ?> - <?= htmlspecialchars($selectedTahunSelesai) ?>
                    (<?= $selectedFilterBy === 'tahun_lulus' ? 'Tahun Lulus' : 'Angkatan' ?>)
                    <?php if ($selectedRoleType !== 'all'): ?>
                    | <?= $selectedRoleType === 'pembimbing' ? 'Hanya Pembimbing' : 'Hanya Penguji' ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="historicalWorkloadTable">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Dosen</th>
                                <th class="text-center">Total Bimbingan</th>
                                <th class="text-center">Total Pengujian</th>
                                <th class="text-center">Detail Per Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="small text-muted mt-2">
                    <span class="badge bg-primary me-2"> &nbsp; </span> Bimbingan
                    <span class="badge bg-success ms-3 me-2"> &nbsp; </span> Pengujian
                    <span class="ms-3">Klik "Lihat Detail" untuk breakdown per periode dan daftar lengkap mahasiswa</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentDetailsModalLabel">
                    <i class="fas fa-users me-2"></i>Detail Mahasiswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="studentDetailsLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data mahasiswa...</p>
                </div>
                <div id="studentDetailsContent" style="display: none;">
                    <div id="studentDetailsInfo" class="alert alert-info mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle" id="studentDetailsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>NIM</th>
                                    <th>Angkatan</th>
                                    <th>Status</th>
                                    <th>Peran</th>
                                </tr>
                            </thead>
                            <tbody id="studentDetailsTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="studentDetailsError" class="alert alert-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Build initial URL parameters from PHP values
    var initialParams = [];
    initialParams.push('tahun_mulai=<?= urlencode($selectedTahunMulai) ?>');
    initialParams.push('tahun_selesai=<?= urlencode($selectedTahunSelesai) ?>');
    initialParams.push('filter_by=<?= urlencode($selectedFilterBy) ?>');
    initialParams.push('role_type=<?= urlencode($selectedRoleType) ?>');
    <?php if ($currentSearch): ?>
    initialParams.push('search=<?= urlencode($currentSearch) ?>');
    <?php endif; ?>
    var initialUrl = '/reports/historical-workload/data?' + initialParams.join('&');
    
    var table = $('#historicalWorkloadTable').DataTable({
        language: {
            url: "/public/vendor/datatables/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        searching: false, // Disable DataTables built-in search
        dom: '<"top">rt<"bottom"lip>', // Remove default search box
        ajax: initialUrl,
        columns: [
            {
                data: 1,
                render: function(data, type, row) {
                    return '<i class="fas fa-user-tie text-secondary me-2"></i><strong>' + data + '</strong>';
                }
            },
            {
                data: 2,
                render: function(data, type, row) {
                    return '<span class="badge bg-primary px-3 py-2">' + data + ' mahasiswa</span>';
                }
            },
            {
                data: 3,
                render: function(data, type, row) {
                    return '<span class="badge bg-success px-3 py-2">' + data + ' mahasiswa</span>';
                }
            },
            {
                data: 0,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-outline-info" type="button" onclick="loadStudentDetails(' + data + ')"><i class="fas fa-list me-1"></i> Lihat Detail</button>';
                }
            }
        ]
    });
    
    // Store reference to table for use in submit handler
    var historicalWorkloadTable = table;
    
    // Override native submit method to intercept direct calls
    var historicalForm = $('#historicalWorkloadFilterForm')[0];
    var originalSubmit = historicalForm.submit;
    historicalForm.submit = function() {
        console.log('Native form.submit() intercepted, triggering AJAX instead');
        // Trigger our AJAX handler instead of native submit
        $('#historicalWorkloadFilterForm').trigger('submit-custom');
        return false; // Prevent native submit
    };
    
    // Custom submit handler that does the AJAX call
    $('#historicalWorkloadFilterForm').on('submit-custom', function() {
        console.log('Custom submit triggered, processing form data');
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        
        // Build query parameters (only include non-empty values)
        var queryParams = Object.keys(params)
            .filter(function(key) {
                return params[key] !== '';
            })
            .map(function(key) {
                return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            })
            .join('&');
        
        // Build new URL
        var newUrl = '/reports/historical-workload/data';
        if (queryParams) {
            newUrl += '?' + queryParams;
        }
        
        // Update ajax URL and reload
        historicalWorkloadTable.ajax.url(newUrl).load();
    });
    
    // Also bind to regular submit event (for button clicks)
    $('#historicalWorkloadFilterForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Trigger our custom handler
        $(this).trigger('submit-custom');
        return false;
    });
});

function loadStudentDetails(lecturerId) {
    // Get current filter values from form
    var form = document.getElementById('historicalWorkloadFilterForm');
    var tahunMulai = form.querySelector('[name="tahun_mulai"]').value;
    var tahunSelesai = form.querySelector('[name="tahun_selesai"]').value;
    var filterBy = form.querySelector('[name="filter_by"]').value;
    var roleType = form.querySelector('[name="role_type"]').value;
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
    modal.show();
    
    // Reset modal state
    document.getElementById('studentDetailsLoading').style.display = 'block';
    document.getElementById('studentDetailsContent').style.display = 'none';
    document.getElementById('studentDetailsError').style.display = 'none';
    document.getElementById('studentDetailsTableBody').innerHTML = '';
    
    // Fetch student details via AJAX
    var params = new URLSearchParams({
        lecturer_id: lecturerId,
        role_type: roleType,
        filter_by: filterBy,
        tahun_mulai: tahunMulai,
        tahun_selesai: tahunSelesai
    });
    
    fetch('/reports/historical-workload-details?' + params.toString())
        .then(response => response.json())
        .then(data => {
            document.getElementById('studentDetailsLoading').style.display = 'none';
            
            if (data.success) {
                displayStudentDetails(data.students, lecturerId);
            } else {
                showError(data.error || 'Gagal memuat data mahasiswa');
            }
        })
        .catch(error => {
            document.getElementById('studentDetailsLoading').style.display = 'none';
            showError('Gagal memuat data: ' + error.message);
        });
    
    function displayStudentDetails(students, lecturerId) {
        document.getElementById('studentDetailsContent').style.display = 'block';
        
        // Update info
        var info = 'Total: <strong>' + students.length + '</strong> mahasiswa';
        document.getElementById('studentDetailsInfo').innerHTML = info;
        
        // Populate table
        var tbody = document.getElementById('studentDetailsTableBody');
        var html = '';
        
        if (students.length === 0) {
            html = '<tr><td colspan="6" class="text-center">Tidak ada data mahasiswa</td></tr>';
        } else {
            students.forEach(function(student, index) {
                var statusBadge = student.status === 'LULUS'
                    ? '<span class="badge bg-success">Lulus</span>'
                    : '<span class="badge bg-primary">Aktif</span>';
                
                var roleBadge = student.role === 'pembimbing'
                    ? '<span class="badge bg-primary">Pembimbing</span>'
                    : '<span class="badge bg-success">Penguji</span>';
                
                html += '<tr>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td><strong>' + escapeHtml(student.nama) + '</strong></td>';
                html += '<td>' + escapeHtml(student.nim) + '</td>';
                html += '<td>' + escapeHtml(student.angkatan) + '</td>';
                html += '<td>' + statusBadge + '</td>';
                html += '<td>' + roleBadge + '</td>';
                html += '</tr>';
            });
        }
        
        tbody.innerHTML = html;
    }
    
    function showError(message) {
        document.getElementById('studentDetailsError').style.display = 'block';
        document.getElementById('studentDetailsError').textContent = message;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
</script>
