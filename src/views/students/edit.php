<?php
$student = isset($student) ? $student : [];
?>

<div class="row">
    <div class="col-12">
        <h2>Edit Mahasiswa</h2>
        <p>Edit data mahasiswa</p>
    </div>
</div>

<?php if (empty($student)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <p>Data mahasiswa tidak ditemukan atau tidak valid.</p>
            <a href="/students" class="btn btn-primary">Kembali ke Daftar Mahasiswa</a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Mahasiswa</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/students/<?= htmlspecialchars($student['id']) ?>/update">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="nim" class="form-label">NIM</label>
                        <input type="text" class="form-control" id="nim" name="nim" value="<?= htmlspecialchars($student['nim'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($student['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="angkatan" class="form-label">Angkatan</label>
                        <input type="number" class="form-control" id="angkatan" name="angkatan" min="1900" max="2100" value="<?= htmlspecialchars($student['angkatan'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="semester_masuk" class="form-label">Semester Masuk</label>
                        <input type="text" class="form-control" id="semester_masuk" name="semester_masuk" value="<?= htmlspecialchars($student['semester_masuk'] ?? '') ?>" required>
                        <div class="form-text">
                            Format: 23241 (2023/2024 Ganjil), 23242 (2023/2024 Genap), atau 2324 (2023/2024 Ganjil)
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="AKTIF" <?= (isset($student['status']) && $student['status'] == 'AKTIF') ? 'selected' : '' ?>>AKTIF</option>
                            <option value="CUTI" <?= (isset($student['status']) && $student['status'] == 'CUTI') ? 'selected' : '' ?>>CUTI</option>
                            <option value="NON-AKTIF" <?= (isset($student['status']) && $student['status'] == 'NON-AKTIF') ? 'selected' : '' ?>>NON-AKTIF</option>
                            <option value="MENGULANG" <?= (isset($student['status']) && $student['status'] == 'MENGULANG') ? 'selected' : '' ?>>MENGULANG</option>
                            <option value="LULUS" <?= (isset($student['status']) && $student['status'] == 'LULUS') ? 'selected' : '' ?>>LULUS</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="/students" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Panduan</h5>
            </div>
            <div class="card-body">
                <h6>Format Semester Masuk</h6>
                <p>Gunakan format berikut untuk kolom "Semester Masuk":</p>
                <ul>
                    <li><strong>23241</strong> untuk tahun ajaran 2023/2024 semester ganjil</li>
                    <li><strong>23242</strong> untuk tahun ajaran 2023/2024 semester genap</li>
                    <li><strong>2324</strong> untuk tahun ajaran 2023/2024 semester ganjil (format alternatif)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
