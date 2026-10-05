<?php
$students = $students ?? [];
$angkatanList = $angkatanList ?? [];
$selectedAngkatan = $selectedAngkatan ?? null;
$currentSearch = $currentSearch ?? '';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="/reports/summary" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="angkatan" class="form-label">Angkatan</label>
                        <select name="angkatan" id="angkatan" class="form-select">
                            <option value="">Semua Angkatan</option>
                            <?php foreach ($angkatanList as $angkatan): ?>
                                <option value="<?= htmlspecialchars($angkatan) ?>" <?= (string)$selectedAngkatan === (string)$angkatan ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($angkatan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Cari Mahasiswa</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="NIM atau Nama"
                               value="<?= htmlspecialchars($currentSearch) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Terapkan
                        </button>
                        <a href="/reports/summary" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h2>Rekapitulasi Mahasiswa</h2>
        <p class="text-muted">Ringkasan status akademik, pembimbing, penguji, jadwal, dan nilai mahasiswa.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="summaryTable">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="align-middle">Mahasiswa</th>
                                <th rowspan="2" class="align-middle">Judul Skripsi</th>
                                <th colspan="2" class="text-center">Dosen Pembimbing</th>
                                <th colspan="3" class="text-center">Dosen Penguji</th>
                                <th colspan="3" class="text-center">Jadwal & Nilai</th>
                            </tr>
                            <tr>
                                <th>Pembimbing 1</th>
                                <th>Pembimbing 2</th>
                                <th>Ketua</th>
                                <th>Anggota 1</th>
                                <th>Anggota 2</th>
                                <th>Sempro</th>
                                <th>Semhas</th>
                                <th>Sidang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="14" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#summaryTable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '/reports/summary/data',
            data: function(d) {
                <?php if ($selectedAngkatan): ?>
                d.angkatan = <?= json_encode($selectedAngkatan) ?>;
                <?php endif; ?>
                <?php if ($currentSearch): ?>
                d.search = <?= json_encode($currentSearch) ?>;
                <?php endif; ?>
            }
        },
        columns: [
            {
                data: 0,
                render: function(data, type, row) {
                    return '<strong>' + row[1] + '</strong><br><small>' + row[0] + '</small>';
                }
            },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6 },
            { data: 7 },
            {
                data: 8,
                render: function(data, type, row) {
                    let html = '';
                    if (row[8]) {
                        html += '<div><small>' + row[8] + '</small></div>';
                    }
                    if (row[9]) {
                        html += '<span class="badge bg-primary">' + row[9] + '</span>';
                    } else {
                        html += '<span class="text-muted small">-</span>';
                    }
                    return html;
                }
            },
            {
                data: 10,
                render: function(data, type, row) {
                    let html = '';
                    if (row[10]) {
                        html += '<div><small>' + row[10] + '</small></div>';
                    }
                    if (row[11]) {
                        html += '<span class="badge bg-primary">' + row[11] + '</span>';
                    } else {
                        html += '<span class="text-muted small">-</span>';
                    }
                    return html;
                }
            },
            {
                data: 12,
                render: function(data, type, row) {
                    let html = '';
                    if (row[12]) {
                        html += '<div><small>' + row[12] + '</small></div>';
                    }
                    if (row[13]) {
                        html += '<span class="badge bg-success">' + row[13] + '</span>';
                    } else {
                        html += '<span class="text-muted small">-</span>';
                    }
                    return html;
                }
            }
        ]
    });
    
    // Update AJAX request when filter form is submitted
    $('form[method="GET"]').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var params = {};
        formData.forEach(function(item) {
            params[item.name] = item.value;
        });
        table.settings()[0].ajax.data = function(d) {
            d.angkatan = params.angkatan || '';
            d.search = params.search || '';
        };
        table.ajax.reload();
    });
});
</script>
