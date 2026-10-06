<div class="row">
    <div class="col-12">
        <h2>Profil Mahasiswa</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>NIM</th>
                        <td><?= htmlspecialchars($student->nim) ?></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td><?= htmlspecialchars($student->name) ?></td>
                    </tr>
                    <tr>
                        <th>Angkatan</th>
                        <td><?= htmlspecialchars($student->angkatan) ?></td>
                    </tr>
                    <tr>
                        <th>Semester Masuk</th>
                        <td><?= htmlspecialchars($student->semester_masuk) ?></td>
                    </tr>
                    <tr>
                        <th>Semester Lulus</th>
                        <td><?= htmlspecialchars($student->semester_lulus ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <?php
                            switch ($student->status) {
                                case 'AKTIF':
                                    echo '<span class="badge bg-success">Aktif</span>';
                                    break;
                                case 'CUTI':
                                    echo '<span class="badge bg-warning">Cuti</span>';
                                    break;
                            case 'NON-AKTIF':
                                echo '<span class="badge bg-secondary">Non-Aktif</span>';
                                break;
                                case 'MENGULANG':
                                    echo '<span class="badge bg-danger">Mengulang</span>';
                                    break;
                                case 'LULUS':
                                    echo '<span class="badge bg-info">Lulus</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-light text-dark">' . htmlspecialchars($student->status ?? '-') . '</span>';
                                    break;
                            }
                            ?>
                        </td>
                    </tr>
                </table>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Dosen Pembimbing & Penguji</h5>
                <?php if (isset($currentUserRole) && in_array($currentUserRole, ['kombi', 'superadmin'])): ?>
                    <a href="/assignments/change/<?= $student->id ?>" class="btn btn-primary btn-sm">Ganti Dosen</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Pembimbing 1</th>
                        <td><?= htmlspecialchars($assignments['pembimbing_1'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Pembimbing 2</th>
                        <td><?= htmlspecialchars($assignments['pembimbing_2'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Penguji 1</th>
                        <td><?= htmlspecialchars($assignments['penguji_1'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Penguji 2</th>
                        <td><?= htmlspecialchars($assignments['penguji_2'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Penguji 3</th>
                        <td><?= htmlspecialchars($assignments['penguji_3'] ?? '-') ?></td>
                    </tr>
                </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Aksi</h5>
            </div>
            <div class="card-body">
                <a href="/students/<?= $student->id ?>/edit" class="btn btn-warning w-100 mb-2">Edit Mahasiswa</a>
                <a href="/students/<?= $student->id ?>/delete" class="btn btn-danger w-100">Hapus Mahasiswa</a>
                <?php if (isset($currentUserRole) && in_array($currentUserRole, ['kombi', 'superadmin'])): ?>
                    <a href="/assignments/history/<?= $student->id ?>" class="btn btn-info w-100 mt-2">Riwayat Pergantian</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <a href="/students" class="btn btn-secondary">Kembali ke Daftar Mahasiswa</a>
    </div>
</div>
