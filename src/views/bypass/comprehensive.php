"<?php
\$student = \$student ?? [];
\$title = \$title ?? null;
\$lecturers = \$lecturers ?? [];
\$currentAssignments = \$currentAssignments ?? [];
\$evaluationMap = \$evaluationMap ?? [];
\$stageLabels = \$stageLabels ?? [];
\$selectedStage = \$selectedStage ?? null;
?>

<div class=\"row\">
    <div class=\"col-12\">
        <h2>Bypass Komprehensif</h2>
        <p class=\"text-muted\">Form bypass lengkap untuk mengisi data mahasiswa terdahulu yang sudah lulus.</p>
    </div>
</div>

<?php if (!empty(\$error)): ?>
    <div class=\"alert alert-danger\"><?= htmlspecialchars(\$error) ?></div>
<?php endif; ?>
<?php if (!empty(\$success)): ?>
    <div class=\"alert alert-success\"><?= htmlspecialchars(\$success) ?></div>
<?php endif; ?>

<div class=\"alert alert-info\">
    <strong>Fitur Bypass Komprehensif:</strong>
    <ul class=\"mb-0\">
        <li>Bisa digunakan untuk semua tahap (sempro, semhas, pra-ujian, ujian)</li>
        <li>Bisa memilih judul skripsi dari database</li>
        <li>Bisa memilih dosen pembimbing dan penguji dari database</li>
        <li>Bisa digunakan untuk mahasiswa yang sudah lulus</li>
    </ul>
</div>

<div class=\"card\">
    <div class=\"card-body\">
        <h5>Informasi Mahasiswa</h5>
        <dl class=\"row\">
            <dt class=\"col-sm-3\">NIM</dt>
            <dd class=\"col-sm-9\"><?= htmlspecialchars(\$student['nim'] ?? '') ?></dd>
            
            <dt class=\"col-sm-3\">Nama</dt>
            <dd class=\"col-sm-9\"><?= htmlspecialchars(\$student['name'] ?? '') ?></dd>
            
            <dt class=\"col-sm-3\">Status</dt>
            <dd class=\"col-sm-9\">
                <span class=\"badge bg-<?= (\$student['status'] ?? '') === 'LULUS' ? 'success' : 'warning' ?>\">
                    <?= htmlspecialchars(\$student['status'] ?? 'BELUM LULUS') ?>
                </span>
            </dd>
        </dl>
        
        <div class=\"mt-3\">
            <a href=\"/scores/submit\" class=\"btn btn-secondary\">Kembali</a>
            <a href=\"/evaluations/bypass/<?= \$student['id'] ?? '' ?>/ujian\" class=\"btn btn-warning\">Bypass Nilai Ujian</a>
        </div>
    </div>
</div>"