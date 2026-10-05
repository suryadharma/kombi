<?php
$student = isset($student) ? $student : [];
$evaluation = isset($evaluation) ? $evaluation : null;
?>

<div class="row">
    <div class="col-12">
        <h2>Form Penilaian Seminar Proposal</h2>
        <p>Menilai mahasiswa berdasarkan performansi dan isi materi seminar</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">NIM</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($student['nim'] ?? '') ?></dd>
                    
                    <dt class="col-sm-4">Nama</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($student['name'] ?? '') ?></dd>
                    
                    <dt class="col-sm-4">Judul Skripsi</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($student['thesis_title'] ?? '-') ?></dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Informasi Penilai</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Nama</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($_SESSION['user_name']) ?></dd>
                    
                    <dt class="col-sm-4">Peran</dt>
                    <dd class="col-sm-8">
                        <?php 
                        switch ($_SESSION['role']) {
                            case 'dosen_pembimbing':
                                echo 'Pembimbing';
                                break;
                            case 'dosen_penguji':
                                echo 'Penguji';
                                break;
                            default:
                                echo 'Administrator';
                        }
                        ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Form Penilaian</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/evaluations/proposal/<?= $student['id'] ?? '' ?>/save">
                    <?= Csrf::field(); ?>
                    <!-- Aspek Performansi -->
                    <div class="mb-5">
                        <h4>Aspek Performansi (Bobot 40%)</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="60%">Indikator</th>
                                        <th width="15%">Bobot</th>
                                        <th width="20%">Nilai (0-100)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Komunikasi (lancar, jelas, sopan)</td>
                                        <td class="text-center">10%</td>
                                        <td>
                                            <input type="number" class="form-control performance-score" 
                                                   name="performance_score_1" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['performance_score_1'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Sistematika presentasi</td>
                                        <td class="text-center">15%</td>
                                        <td>
                                            <input type="number" class="form-control performance-score" 
                                                   name="performance_score_2" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['performance_score_2'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Penguasaan materi presentasi</td>
                                        <td class="text-center">20%</td>
                                        <td>
                                            <input type="number" class="form-control performance-score" 
                                                   name="performance_score_3" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['performance_score_3'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Kualitas presentasi (media/visual)</td>
                                        <td class="text-center">25%</td>
                                        <td>
                                            <input type="number" class="form-control performance-score" 
                                                   name="performance_score_4" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['performance_score_4'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Kemampuan merespon pertanyaan</td>
                                        <td class="text-center">30%</td>
                                        <td>
                                            <input type="number" class="form-control performance-score" 
                                                   name="performance_score_5" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['performance_score_5'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Nilai Akhir Aspek Performansi:</strong></td>
                                        <td colspan="2">
                                            <input type="text" class="form-control" id="performance_total" readonly>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Aspek Isi Materi Seminar -->
                    <div class="mb-5">
                        <h4>Aspek Isi Materi Seminar (Bobot 60%)</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="60%">Indikator</th>
                                        <th width="15%">Bobot</th>
                                        <th width="20%">Nilai (0-100)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Relevansi dengan bidang studi</td>
                                        <td class="text-center">30%</td>
                                        <td>
                                            <input type="number" class="form-control content-score" 
                                                   name="content_score_1" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['content_score_1'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Kedalaman kajian pustaka</td>
                                        <td class="text-center">40%</td>
                                        <td>
                                            <input type="number" class="form-control content-score" 
                                                   name="content_score_2" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['content_score_2'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Metodologi penelitian</td>
                                        <td class="text-center">30%</td>
                                        <td>
                                            <input type="number" class="form-control content-score" 
                                                   name="content_score_3" 
                                                   min="0" max="100" 
                                                   value="<?= $evaluation ? $evaluation['content_score_3'] : '' ?>" 
                                                   required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Nilai Akhir Aspek Isi Materi:</strong></td>
                                        <td colspan="2">
                                            <input type="text" class="form-control" id="content_total" readonly>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Total Nilai -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Aspek</th>
                                                <th>Bobot</th>
                                                <th>Nilai Akhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Performansi</td>
                                                <td class="text-center">40%</td>
                                                <td id="performance_final">0.00</td>
                                            </tr>
                                            <tr>
                                                <td>Isi Materi Seminar</td>
                                                <td class="text-center">60%</td>
                                                <td id="content_final">0.00</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-end"><strong>TOTAL NILAI SEMINAR PROPOSAL:</strong></td>
                                                <td><strong id="final_score">0.00</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
                        <a href="/students" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all input elements
    const performanceScores = document.querySelectorAll('.performance-score');
    const contentScores = document.querySelectorAll('.content-score');
    
    // Add event listeners to performance scores
    performanceScores.forEach(input => {
        input.addEventListener('input', calculatePerformance);
    });
    
    // Add event listeners to content scores
    contentScores.forEach(input => {
        input.addEventListener('input', calculateContent);
    });
    
    // Calculate initial values if form is in edit mode
    if (<?= $evaluation ? 'true' : 'false' ?>) {
        calculatePerformance();
        calculateContent();
        calculateFinal();
    }
    
    // Performance calculation
    function calculatePerformance() {
        const scores = Array.from(performanceScores).map(input => parseFloat(input.value) || 0);
        
        // Calculate weighted scores
        const weightedScores = [
            scores[0] * 0.10,
            scores[1] * 0.15,
            scores[2] * 0.20,
            scores[3] * 0.25,
            scores[4] * 0.30
        ];
        
        // Sum weighted scores
        const total = weightedScores.reduce((sum, score) => sum + score, 0);
        
        // Update UI
        document.getElementById('performance_total').value = total.toFixed(2);
        document.getElementById('performance_final').textContent = (total * 0.40).toFixed(2);
        
        calculateFinal();
    }
    
    // Content calculation
    function calculateContent() {
        const scores = Array.from(contentScores).map(input => parseFloat(input.value) || 0);
        
        // Calculate weighted scores
        const weightedScores = [
            scores[0] * 0.30,
            scores[1] * 0.40,
            scores[2] * 0.30
        ];
        
        // Sum weighted scores
        const total = weightedScores.reduce((sum, score) => sum + score, 0);
        
        // Update UI
        document.getElementById('content_total').value = total.toFixed(2);
        document.getElementById('content_final').textContent = (total * 0.60).toFixed(2);
        
        calculateFinal();
    }
    
    // Final calculation
    function calculateFinal() {
        const performanceFinal = parseFloat(document.getElementById('performance_final').textContent) || 0;
        const contentFinal = parseFloat(document.getElementById('content_final').textContent) || 0;
        const final = performanceFinal + contentFinal;
        
        document.getElementById('final_score').textContent = final.toFixed(2);
    }
});
</script>
