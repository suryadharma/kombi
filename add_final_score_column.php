"<?php
// Script untuk menambahkan kolom Nilai Akhir Skripsi di view scores/submit.php

$content = file_get_contents('src/views/scores/submit.php');

// 1. Tambahkan kolom di header
$header_pattern = '/<th>NIM<\/th>\s*<th>Nama<\/th>\s*(<\\?php foreach \\(\\$availableStages as \\$stageCode => \\$stageLabel\\): \\?>\\s*<th><\\?= htmlspecialchars\\(\\$stageLabel\\) \\?><\\/th>\\s*<\\?php endforeach; \\?>\\s*)<\/tr>/';
$header_replacement = '<th>NIM</th>
                                    <th>Nama</th>
                                    $1
                                    <th>Nilai Akhir Skripsi</th>
                                </tr>';

$content = preg_replace($header_pattern, $header_replacement, $content);

// 2. Tambahkan data di setiap baris (setelah loop tahap)
$data_pattern = '/(<\\/td>\\s*<\\?php endforeach; \\?>\\s*)(<\\/tr>\\s*<\\?php endforeach; \\?>)/';
$data_replacement = '$1
                                        <td>
                                            <?php if ($student[\'final_score_avg\'] !== null): ?>
                                                <div class=\"d-flex flex-column gap-1\">
                                                    <span class=\"badge bg-primary\">
                                                        <?= number_format($student[\'final_score_avg\'], 2) ?>
                                                    </span>
                                                    <?php if ($student[\'final_score_letter\']): ?>
                                                        <span class=\"badge bg-success\">
                                                            <?= htmlspecialchars($student[\'final_score_letter\']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (isset($student[\'final_breakdown\'])): ?>
                                                        <button type=\"button\" class=\"btn btn-sm btn-outline-info\" 
                                                                data-bs-toggle=\"popover\" 
                                                                data-bs-title=\"Detail Perhitungan\"
                                                                data-bs-content=\"<?= htmlspecialchars(json_encode($student[\'final_breakdown\'] ?? [])) ?>\">
                                                            <i class=\"fas fa-info-circle\"></i> Detail
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class=\"text-muted small\">Belum lengkap</span>
                                            <?php endif; ?>
                                        </td>
                                    $2';

$content = preg_replace($data_pattern, $data_replacement, $content);

// Simpan perubahan
file_put_contents('src/views/scores/submit.php', $content);

echo \"Kolom Nilai Akhir Skripsi berhasil ditambahkan!\\n\";
?>
"