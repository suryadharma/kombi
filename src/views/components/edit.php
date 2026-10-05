<?php
$component = isset($component) ? $component : [];
?>

<div class="row">
    <div class="col-12">
        <h2>Edit Komponen Penilaian</h2>
        <p>Edit komponen penilaian</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Form Komponen Penilaian</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="/components/<?= $component['id'] ?? '' ?>/update">
                    <?= Csrf::field(); ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Komponen</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($component['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stage" class="form-label">Tahap</label>
                        <select class="form-select" id="stage" name="stage" required>
                            <option value="">Pilih Tahap</option>
                            <option value="sempro" <?= (isset($component['stage']) && $component['stage'] == 'sempro') ? 'selected' : '' ?>>Seminar Proposal</option>
                            <option value="semhas" <?= (isset($component['stage']) && $component['stage'] == 'semhas') ? 'selected' : '' ?>>Seminar Hasil</option>
                            <option value="pra-ujian" <?= (isset($component['stage']) && $component['stage'] == 'pra-ujian') ? 'selected' : '' ?>>Pra-Ujian Skripsi</option>
                            <option value="ujian" <?= (isset($component['stage']) && $component['stage'] == 'ujian') ? 'selected' : '' ?>>Ujian Skripsi</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="weight" class="form-label">Bobot</label>
                        <input type="number" class="form-control" id="weight" name="weight" min="0" max="1" step="0.0001" value="<?= htmlspecialchars($component['weight'] ?? '') ?>" required>
                        <div class="form-text">Bobot dalam bentuk desimal (0.0000 - 1.0000), contoh: 0.05 untuk 5%</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Urutan</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" min="0" value="<?= htmlspecialchars($component['sort_order'] ?? '0') ?>">
                        <div class="form-text">Urutan penampilan komponen</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($component['description'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="/components" class="btn btn-secondary">Kembali</a>
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
                <h6>Informasi</h6>
                <ul>
                    <li>Nama Komponen: Nama dari komponen penilaian</li>
                    <li>Tahap: Tahap seminar/ujian tempat komponen ini digunakan</li>
                    <li>Bobot: Bobot komponen dalam bentuk desimal (0.0000 - 1.0000)</li>
                    <li>Urutan: Urutan penampilan komponen dalam form penilaian</li>
                    <li>Deskripsi: Penjelasan detail tentang komponen penilaian</li>
                </ul>
                
                <h6>Catatan</h6>
                <ul>
                    <li>Total bobot semua komponen dalam satu tahap harus 1.0000 (100%)</li>
                    <li>Sistem akan secara otomatis menghitung nilai berdasarkan bobot</li>
                    <li>Contoh bobot: 0.05 = 5%, 0.25 = 25%, 0.5 = 50%</li>
                </ul>
            </div>
        </div>
    </div>
</div>
