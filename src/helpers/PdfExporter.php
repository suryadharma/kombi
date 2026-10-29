<?php

$fpdfBootstrap = __DIR__ . '/../libs/fpdf/fpdf.php';
if (!class_exists('FPDF') && file_exists($fpdfBootstrap)) {
    require_once $fpdfBootstrap;
}

class PdfExporter
{
    private static bool $booted = false;

    private static array $stageMeta = [
        'sempro' => [
            'label' => 'Seminar Proposal',
            'title' => 'Form Penilaian Seminar Proposal',
        ],
        'semhas' => [
            'label' => 'Seminar Hasil',
            'title' => 'Form Penilaian Seminar Hasil',
        ],
        'pra-ujian' => [
            'label' => 'Pra-Ujian Skripsi',
            'title' => 'Form Penilaian Pra-Ujian Skripsi',
        ],
        'ujian' => [
            'label' => 'Sidang Skripsi',
            'title' => 'Rekap Nilai Sidang Skripsi',
        ],
    ];

    public static function outputStageEvaluation(array $student, string $stageCode, array $evaluations, array $defaultComponents = [], ?array $schedule = null): void
    {
        self::boot();

        $stageCodeNormalized = strtolower($stageCode);

        if (!isset(self::$stageMeta[$stageCodeNormalized])) {
            throw new InvalidArgumentException('Tahap tidak dikenali untuk PDF.');
        }

        $stage = self::$stageMeta[$stageCodeNormalized];
        $showFinalAverage = false;
        $pdf = new KombiPDF('P', 'mm', 'A4');
        $pdf->SetTitle($stage['title']);
        $pdf->SetAuthor(AppSettings::getShortName());
        $pdf->SetMargins(12, 16, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $projectRoot = defined('BASE_PATH') ? dirname(BASE_PATH) : dirname(__DIR__, 2);
        $letterheadLogo = $projectRoot . '/' . ltrim(AppSettings::getLetterheadLogo(), '/');
        $pdf->setLetterhead($letterheadLogo, AppSettings::getOrganizationInfo());
        $pdf->setRowPadding(0.3);
        $pdf->setStageInfo($stage, $student, $schedule);
        $pdf->AddPage();

        // Note: renderStageIntro is no longer needed here as the header now includes student info
        // self::renderStageIntro($pdf, $stage, $student, $schedule);

        if (empty($evaluations)) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 5, 'Belum ada nilai pada tahap ini.', 0, 1);
        }

        foreach ($evaluations as $index => $evaluation) {
            if ($index > 0) {
                $pdf->AddPage();
            }
            $roleLabel = $evaluation['role_label'] ?? ($evaluation['assignment_role'] ?? $evaluation['evaluator_role'] ?? '-');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 6, strtoupper($roleLabel) . ' - ' . ($evaluation['evaluator_name'] ?? '-'), 0, 1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(40, 5, 'NIP/NIDN', 0, 0);
            $pdf->Cell(0, 5, $evaluation['nip'] ?? '-', 0, 1);
            if ($showFinalAverage) {
                $pdf->Cell(40, 5, 'Nilai Akhir (Rata-rata)', 0, 0);
                $pdf->Cell(0, 5, isset($evaluation['final_score']) ? number_format((float) $evaluation['final_score'], 2) : '-', 0, 1);
            }
            if (isset($evaluation['total_score'])) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(40, 5, 'Jumlah Nilai x Bobot', 0, 0);
                $pdf->Cell(0, 5, number_format((float) $evaluation['total_score'], 2), 0, 1);
                $pdf->SetFont('Arial', '', 9);
            }

            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'B', 9);
            [$componentWidth, $weightWidth, $scoreWidth, $weightedWidth] = self::getComponentColumnWidths($pdf);
            self::renderComponentRow($pdf, [
                [
                    'text' => 'Komponen Penilaian',
                    'width' => $componentWidth,
                    'align' => 'L',
                    'font' => ['Arial', 'B', 9],
                    'shrink_to_fit' => true,
                    'min_font_size' => 8.0,
                    'valign' => 'M',
                ],
                [
                    'text' => 'Bobot (%)',
                    'width' => $weightWidth,
                    'align' => 'C',
                    'font' => ['Arial', 'B', 9],
                    'valign' => 'M',
                ],
                [
                    'text' => 'Nilai',
                    'width' => $scoreWidth,
                    'align' => 'C',
                    'font' => ['Arial', 'B', 9],
                    'valign' => 'M',
                ],
                [
                    'text' => 'N x B',
                    'width' => $weightedWidth,
                    'align' => 'C',
                    'font' => ['Arial', 'B', 9],
                    'valign' => 'M',
                ],
            ], 3.2);
            $pdf->SetFont('Arial', '', 9);

            $components = $evaluation['components'] ?? $defaultComponents;
            if (empty($components)) {
                $pdf->Cell(0, 6, 'Belum ada rincian komponen.', 1, 1, 'C');
            } else {
                foreach ($components as $component) {
                    $weightRaw = isset($component['weight']) ? (float) $component['weight'] : 0.0;
                    $weight = $weightRaw <= 1 ? $weightRaw * 100 : $weightRaw;
                    $score = isset($component['score']) ? (float) $component['score'] : null;
                    $weightFraction = $weightRaw > 1 ? $weightRaw / 100 : $weightRaw;
                    $weighted = ($weightFraction > 0 && $score !== null) ? $score * $weightFraction : null;
                    self::renderComponentRow($pdf, [
                        [
                            'text' => $component['name'] ?? '-',
                            'width' => $componentWidth,
                            'align' => 'L',
                            'font' => ['Arial', '', 9],
                            'shrink_to_fit' => true,
                            'min_font_size' => 6.5,
                        ],
                        [
                            'text' => number_format($weight, 1),
                            'width' => $weightWidth,
                            'align' => 'C',
                        ],
                        [
                            'text' => $score !== null ? number_format($score, 2) : '-',
                            'width' => $scoreWidth,
                            'align' => 'C',
                        ],
                        [
                            'text' => $weighted !== null ? number_format($weighted, 2) : '-',
                            'width' => $weightedWidth,
                            'align' => 'C',
                        ],
                    ], 3.4);
                }
                $pdf->SetFont('Arial', 'B', 9);
                $sumLabelWidth = $componentWidth + $weightWidth + $scoreWidth;
                if (isset($evaluation['total_score'])) {
                    $pdf->Cell($sumLabelWidth, 6, 'Jumlah Total Nilai x Bobot', 1, 0, 'R');
                    $pdf->Cell($weightedWidth, 6, number_format((float) $evaluation['total_score'], 2), 1, 1, 'C');
                }
                $pdf->SetFont('Arial', '', 9);
            }

            if (!empty($evaluation['notes'])) {
                $pdf->Ln(1);
                $pdf->SetFont('Arial', 'I', 8.5);
                $pdf->MultiCell(0, 4, 'Catatan Dosen: ' . $evaluation['notes']);
            }

            $qrPayload = self::buildQrPayload($student, $stage, $evaluation);
            $qrSize = 22;
            $sigBlockWidth = 55;
            $sigX = $pdf->getLeftMargin() + $pdf->getContentWidth() - $sigBlockWidth;
            $qrX = $sigX + ($sigBlockWidth - $qrSize) / 2;

            $startY = $pdf->GetY() + 2;
            // Prevent orphan signature
            if ($startY + $qrSize + 18 > 285) {
                $pdf->AddPage();
                $startY = $pdf->GetY() + 4;
            }

            $pdf->SetXY($sigX, $startY);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($sigBlockWidth, 4, 'Jember, ' . date('d F Y'), 0, 1, 'C');
            $pdf->SetX($sigX);
            $pdf->Cell($sigBlockWidth, 4, $roleLabel . ',', 0, 1, 'C');

            $qrY = $pdf->GetY() + 0.5;
            $pdf->SetXY($qrX, $qrY);
            self::drawQrOnPdf($pdf, $qrPayload, $qrSize);

            $pdf->SetXY($sigX, $qrY + $qrSize + 0.5);
            $nameText = $evaluation['evaluator_name'] ?? '-';
            $pdf->SetFont('Arial', 'BU', 9);
            $pdf->Cell($sigBlockWidth, 4.5, $nameText, 0, 1, 'C');
            if (!empty($evaluation['nip'])) {
                $pdf->SetX($sigX);
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell($sigBlockWidth, 4, 'NIP. ' . $evaluation['nip'], 0, 1, 'C');
            }
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/pdf');
        $downloadName = self::buildPdfFilename($student, $stageCode, $evaluations);
        header('Content-Disposition: inline; filename="' . $downloadName . '"');
        $pdf->Output('I');
    }

    public static function outputFinalThesisScore(array $student, array $scoreData, ?array $chairperson = null): void
    {
        self::boot();

        $pdf = new KombiPDF('P', 'mm', 'A4');
        $pdf->SetTitle('Nilai Akhir Skripsi');
        $pdf->SetAuthor(AppSettings::getShortName());
        $pdf->SetMargins(12, 22, 12);
        $pdf->SetAutoPageBreak(true, 16);

        $projectRoot = defined('BASE_PATH') ? dirname(BASE_PATH) : dirname(__DIR__, 2);
        $letterheadLogo = $projectRoot . '/' . ltrim(AppSettings::getLetterheadLogo(), '/');
        $pdf->setLetterhead($letterheadLogo, AppSettings::getOrganizationInfo());
        $pdf->setRowPadding(0.4);
        $pdf->AddPage();

        // Title
        $pdf->SetY(max($pdf->getHeaderBottom(), $pdf->GetY()));
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 7, 'TRANSKRIP NILAI SKRIPSI', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5.5, AppSettings::getFullName() . ' (' . AppSettings::getShortName() . ')', 0, 1, 'C');
        $pdf->Ln(5);

        // Student Info
        $infoRows = [
            ['Nama Mahasiswa', $student['name'] ?? '-'],
            ['NIM', $student['nim'] ?? '-'],
            ['Judul Skripsi', $student['title'] ?? '-'],
            ['Angkatan', $student['angkatan'] ?? '-'],
            ['Tanggal Ujian', $student['ujian_date'] ? date('d F Y', strtotime($student['ujian_date'])) : '-'],
        ];

        foreach ($infoRows as $row) {
            self::renderInfoRow($pdf, (string) ($row[0] ?? ''), (string) ($row[1] ?? '-'));
        }

        $pdf->Ln(6);

        // Score Table
        $pdf->SetFont('Arial', 'B', 10);
        $widthLabel = 90;
        $widthScore = 30;
        $widthWeight = 30;
        $widthTotal = 40;

        // Header
        $pdf->Cell($widthLabel, 8, 'Komponen Penilaian', 1, 0, 'C');
        $pdf->Cell($widthWeight, 8, 'Bobot', 1, 0, 'C');
        $pdf->Cell($widthScore, 8, 'Nilai', 1, 0, 'C');
        $pdf->Cell($widthTotal, 8, 'Nilai x Bobot', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);

        // Components
        $components = $scoreData['formula']['parts'] ?? [];
        $finalScore = $scoreData['value'] ?? 0;
        $letterGrade = $scoreData['letter'] ?? '-';
        $isBypass = $scoreData['is_bypass'] ?? false;

        if (empty($components) && $finalScore > 0) {
            // Fallback for direct scores (e.g. bypass without detail)
            $pdf->Cell($widthLabel, 8, $isBypass ? 'Nilai Bypass' : 'Nilai Langsung', 1, 0, 'L');
            $pdf->Cell($widthWeight, 8, '100%', 1, 0, 'C');
            $pdf->Cell($widthScore, 8, number_format($finalScore, 2), 1, 0, 'C');
            $pdf->Cell($widthTotal, 8, number_format($finalScore, 2), 1, 1, 'C');
        } else {
            foreach ($components as $comp) {
                $label = $comp['label'] ?? '-';
                $score = (float) ($comp['value'] ?? 0);
                $weightPct = isset($comp['weight_percent']) ? $comp['weight_percent'] . '%' : '-';
                $weightFrac = $comp['weight_fraction'] ?? 0;
                $total = $score * $weightFrac;

                $pdf->Cell($widthLabel, 8, $label, 1, 0, 'L');
                $pdf->Cell($widthWeight, 8, $weightPct, 1, 0, 'C');
                $pdf->Cell($widthScore, 8, number_format($score, 2), 1, 0, 'C');
                $pdf->Cell($widthTotal, 8, number_format($total, 2), 1, 1, 'C');
            }
        }

        // Final Score Row
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($widthLabel + $widthWeight + $widthScore, 10, 'NILAI AKHIR SKRIPSI', 1, 0, 'R');
        $pdf->Cell($widthTotal, 10, number_format((float) $finalScore, 2), 1, 1, 'C');

        // Letter Grade
        $pdf->Cell($widthLabel + $widthWeight + $widthScore, 10, 'NILAI HURUF', 1, 0, 'R');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell($widthTotal, 10, $letterGrade, 1, 1, 'C');

        $pdf->Ln(15);

        // Signatures
        $sigY = $pdf->GetY();
        if ($sigY + 50 > 270) {
            $pdf->AddPage();
            $sigY = $pdf->GetY();
        }

        $pdf->SetFont('Arial', '', 10);

        // Define Signatory
        if ($isBypass) {
            $signerName = 'Kombi : ' . ($scoreData['bypass_evaluator'] ?? 'Staf KomBi');
            $signerRole = 'Komisi Bimbingan';
            $signerNip = '';
        } else {
            $signerName = $chairperson['name'] ?? 'Penguji Ketua';
            $signerRole = 'Ketua Penguji';
            $signerNip = isset($chairperson['nip']) ? 'NIP. ' . $chairperson['nip'] : '';
        }

        // QR Code Payload - Use verification link
        // Signature Logic
        $sigBlockWidth = 65;
        $sigBlockX = $pdf->getLeftMargin() + $pdf->getContentWidth() - $sigBlockWidth;

        $studentId = (int) ($student['id'] ?? 0);
        $qrPayload = VerificationHelper::generateFinalScoreLink($studentId, null);
        $qrSize = 25;
        $qrX = $sigBlockX + ($sigBlockWidth - $qrSize) / 2;

        $pdf->SetXY($sigBlockX, $sigY);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($sigBlockWidth, 5, 'Jember, ' . date('d F Y'), 0, 1, 'C');
        $pdf->SetX($sigBlockX);
        $pdf->Cell($sigBlockWidth, 5, 'Mengesahkan,', 0, 1, 'C');
        $pdf->SetX($sigBlockX);
        $pdf->Cell($sigBlockWidth, 5, $signerRole . ',', 0, 1, 'C');

        $qrY = $pdf->GetY() + 1;
        $pdf->SetXY($qrX, $qrY);
        self::drawQrOnPdf($pdf, $qrPayload, $qrSize);

        $pdf->SetXY($sigBlockX, $qrY + $qrSize + 1);
        $pdf->SetFont('Arial', 'BU', 10);
        $pdf->Cell($sigBlockWidth, 5, $signerName, 0, 1, 'C');
        if ($signerNip) {
            $pdf->SetX($sigBlockX);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell($sigBlockWidth, 5, $signerNip, 0, 1, 'C');
        }

        $pdf->Ln(10);

        // Output
        $downloadName = 'nilai-akhir-' . preg_replace('/[^a-z0-9]/i', '-', $student['nim']) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $downloadName . '"');
        $pdf->Output('I');
    }

    private static function renderStageIntro(KombiPDF $pdf, array $stage, array $student, ?array $schedule): void
    {
        $pdf->SetY(max($pdf->getHeaderBottom(), $pdf->GetY()));
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(0, 7, strtoupper($stage['title']), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5.5, AppSettings::getFullName() . ' (' . AppSettings::getShortName() . ')', 0, 1, 'C');
        $pdf->Ln(3);

        $infoRows = [
            ['Nama Mahasiswa', $student['name'] ?? '-'],
            ['NIM', $student['nim'] ?? '-'],
            ['Judul Skripsi', $student['title'] ?? '-'],
            ['Angkatan', $student['angkatan'] ?? '-'],
            ['Tahap', $stage['label']],
        ];

        if ($schedule) {
            $infoRows[] = ['Jadwal', $schedule['date_text'] ?? '-'];
            $infoRows[] = ['Ruang', $schedule['room'] ?? '-'];
        }

        foreach ($infoRows as $row) {
            self::renderInfoRow($pdf, (string) ($row[0] ?? ''), (string) ($row[1] ?? '-'));
        }

        $pdf->Ln(2);
    }

    private static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        $fpdfDir = __DIR__ . '/../libs/fpdf';
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', $fpdfDir . '/font/');
        }
        if (!class_exists('FPDF')) {
            require_once $fpdfDir . '/fpdf.php';
        }
        $qrLibPath = __DIR__ . '/../libs/phpqrcode/qrlib.php';
        if (!class_exists('QRcode')) {
            require_once $qrLibPath;
        }

        if (!class_exists('VerificationHelper')) {
            $verHelpPath = __DIR__ . '/VerificationHelper.php';
            if (file_exists($verHelpPath)) {
                require_once $verHelpPath;
            }
        }

        self::$booted = true;
    }

    private static function buildQrPayload(array $student, array $stage, array $evaluation): string
    {
        if (isset($evaluation['id'])) {
            return VerificationHelper::generateEvaluationLink((int) $evaluation['id'], $evaluation['updated_at'] ?? null);
        }

        $data = [
            'kbs' => 'KomBi-TIP',
            'nim' => $student['nim'] ?? '',
            'name' => $student['name'] ?? '',
            'stage' => $stage['label'],
            'lecturer' => $evaluation['evaluator_name'] ?? '',
            'role' => $evaluation['role_label'] ?? ($evaluation['assignment_role'] ?? ''),
            'score' => $evaluation['final_score'] ?? '',
            'updated_at' => $evaluation['updated_at'] ?? null,
        ];
        return 'KBS|' . base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private static function drawQrOnPdf(FPDF $pdf, string $payload, float $size): void
    {
        if (!class_exists('QRcode')) {
            return;
        }
        $matrix = QRcode::text($payload, false, QR_ECLEVEL_M);
        if (!is_array($matrix) || empty($matrix)) {
            return;
        }

        $moduleCount = count($matrix);
        if ($moduleCount <= 0) {
            return;
        }

        $moduleSize = $size / $moduleCount;
        $startX = $pdf->GetX();
        $startY = $pdf->GetY();

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($startX, $startY, $size, $size, 'F');
        $pdf->SetFillColor(0, 0, 0);

        foreach ($matrix as $rowIndex => $row) {
            $rowLength = strlen($row);
            for ($col = 0; $col < $rowLength; $col++) {
                if ($row[$col] === '1') {
                    $x = $startX + ($col * $moduleSize);
                    $y = $startY + ($rowIndex * $moduleSize);
                    $pdf->Rect($x, $y, $moduleSize, $moduleSize, 'F');
                }
            }
        }
    }

    private static function renderInfoRow(KombiPDF $pdf, string $label, string $value): void
    {
        $labelWidth = 48;
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($labelWidth, 5, $label, 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(4, 5, ':', 0, 0, 'C');
        $pdf->MultiCell(0, 5, $value, 0, 'L');
    }

    private static function renderComponentRow(KombiPDF $pdf, array $columns, float $lineHeight = 3.6): void
    {
        $pdf->Row($columns, $lineHeight);
    }

    /**
     * Calculate component table column widths so they always fit the printable area.
     *
     * @return array{float,float,float,float}
     */
    private static function getComponentColumnWidths(KombiPDF $pdf): array
    {
        $contentWidth = $pdf->getContentWidth();
        $componentWidth = round($contentWidth * 0.68, 2);
        $weightWidth = round($contentWidth * 0.12, 2);
        $scoreWidth = round($contentWidth * 0.10, 2);
        $weightedWidth = $contentWidth - ($componentWidth + $weightWidth + $scoreWidth);
        return [$componentWidth, $weightWidth, $scoreWidth, $weightedWidth];
    }

    private static function buildPdfFilename(array $student, string $stageCode, array $evaluations): string
    {
        $segments = [];
        $nim = isset($student['nim']) ? (string) $student['nim'] : '';
        if ($nim !== '') {
            $segments[] = self::sanitizeFilenameSegment($nim);
        }
        $segments[] = self::sanitizeFilenameSegment($stageCode);

        if (count($evaluations) === 1) {
            $evaluation = $evaluations[0];
            $roleSegment = $evaluation['assignment_role'] ?? ($evaluation['role_label'] ?? '');
            if (is_string($roleSegment) && trim($roleSegment) !== '') {
                $segments[] = self::sanitizeFilenameSegment($roleSegment);
            }
            $status = $evaluation['assignment_status'] ?? null;
            if (is_string($status) && $status !== '' && $status !== 'current') {
                $segments[] = self::sanitizeFilenameSegment($status);
            }
        }

        $segments[] = 'nilai';
        $segments = array_values(array_filter($segments, fn($part) => $part !== ''));
        $filename = implode('-', $segments);
        if ($filename === '') {
            $filename = 'nilai';
        }
        return $filename . '.pdf';
    }

    private static function sanitizeFilenameSegment(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
        return trim((string) $value, '-');
    }

    /**
     * Generate PDF for a single lecturer's evaluation and save to file
     *
     * @param array $student Student data
     * @param string $stageCode Stage code (sempro, semhas, pra-ujian, ujian)
     * @param array $evaluation Evaluation data for a single lecturer
     * @param string $filePath Path to save the PDF file
     * @return string The file path
     */
    public static function generateSingleEvaluationPdf(array $student, string $stageCode, array $evaluation, string $filePath): string
    {
        self::boot();
        
        $stageCodeNormalized = strtolower($stageCode);
        
        if (!isset(self::$stageMeta[$stageCodeNormalized])) {
            throw new InvalidArgumentException('Tahap tidak dikenali untuk PDF.');
        }
        
        $stage = self::$stageMeta[$stageCodeNormalized];
        $showFinalAverage = false;
        
        // Create PDF
        $pdf = new KombiPDF('P', 'mm', 'A4');
        $pdf->SetTitle($stage['title']);
        $pdf->SetAuthor(AppSettings::getShortName());
        $pdf->SetMargins(12, 16, 12);
        $pdf->SetAutoPageBreak(true, 12);
        
        $projectRoot = defined('BASE_PATH') ? dirname(BASE_PATH) : dirname(__DIR__, 2);
        $letterheadLogo = $projectRoot . '/' . ltrim(AppSettings::getLetterheadLogo(), '/');
        $pdf->setLetterhead($letterheadLogo, AppSettings::getOrganizationInfo());
        $pdf->setRowPadding(0.3);
        $pdf->setStageInfo($stage, $student, null);
        $pdf->AddPage();
        
        // Render evaluation for this lecturer
        $pdf->SetFont('Arial', 'B', 11);
        $roleLabel = $evaluation['role_label'] ?? ($evaluation['assignment_role'] ?? $evaluation['evaluator_role'] ?? '-');
        $pdf->Cell(0, 6, strtoupper($roleLabel) . ' - ' . ($evaluation['evaluator_name'] ?? '-'), 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(40, 5, 'NIP/NIDN', 0, 0);
        $pdf->Cell(0, 5, $evaluation['nip'] ?? '-', 0, 1);
        
        // Show total score if available
        if (isset($evaluation['total_score'])) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, 'Jumlah Nilai x Bobot', 0, 0);
            $pdf->Cell(0, 5, number_format((float) $evaluation['total_score'], 2), 0, 1);
            $pdf->SetFont('Arial', '', 9);
        }
        
        $pdf->Ln(1);
        $pdf->SetFont('Arial', 'B', 9);
        [$componentWidth, $weightWidth, $scoreWidth, $weightedWidth] = self::getComponentColumnWidths($pdf);
        self::renderComponentRow($pdf, [
            [
                'text' => 'Komponen Penilaian',
                'width' => $componentWidth,
                'align' => 'L',
                'font' => ['Arial', 'B', 9],
                'shrink_to_fit' => true,
                'min_font_size' => 8.0,
                'valign' => 'M',
            ],
            [
                'text' => 'Bobot (%)',
                'width' => $weightWidth,
                'align' => 'C',
                'font' => ['Arial', 'B', 9],
                'valign' => 'M',
            ],
            [
                'text' => 'Nilai',
                'width' => $scoreWidth,
                'align' => 'C',
                'font' => ['Arial', 'B', 9],
                'valign' => 'M',
            ],
            [
                'text' => 'N x B',
                'width' => $weightedWidth,
                'align' => 'C',
                'font' => ['Arial', 'B', 9],
                'valign' => 'M',
            ],
        ], 3.2);
        $pdf->SetFont('Arial', '', 9);
        
        $components = $evaluation['components'] ?? [];
        if (empty($components)) {
            $pdf->Cell(0, 6, 'Belum ada rincian komponen.', 1, 1, 'C');
        } else {
            foreach ($components as $component) {
                $weightRaw = isset($component['weight']) ? (float) $component['weight'] : 0.0;
                $weight = $weightRaw <= 1 ? $weightRaw * 100 : $weightRaw;
                $score = isset($component['score']) ? (float) $component['score'] : null;
                $weightFraction = $weightRaw > 1 ? $weightRaw / 100 : $weightRaw;
                $weighted = ($weightFraction > 0 && $score !== null) ? $score * $weightFraction : null;
                self::renderComponentRow($pdf, [
                    [
                        'text' => $component['name'] ?? '-',
                        'width' => $componentWidth,
                        'align' => 'L',
                        'font' => ['Arial', '', 9],
                        'shrink_to_fit' => true,
                        'min_font_size' => 6.5,
                    ],
                    [
                        'text' => number_format($weight, 1),
                        'width' => $weightWidth,
                        'align' => 'C',
                    ],
                    [
                        'text' => $score !== null ? number_format($score, 2) : '-',
                        'width' => $scoreWidth,
                        'align' => 'C',
                    ],
                    [
                        'text' => $weighted !== null ? number_format($weighted, 2) : '-',
                        'width' => $weightedWidth,
                        'align' => 'C',
                    ],
                ], 3.4);
            }
            $pdf->SetFont('Arial', 'B', 9);
            $sumLabelWidth = $componentWidth + $weightWidth + $scoreWidth;
            if (isset($evaluation['total_score'])) {
                $pdf->Cell($sumLabelWidth, 6, 'Jumlah Total Nilai x Bobot', 1, 0, 'R');
                $pdf->Cell($weightedWidth, 6, number_format((float) $evaluation['total_score'], 2), 1, 1, 'C');
            }
            $pdf->SetFont('Arial', '', 9);
        }
        
        // Notes section
        if (!empty($evaluation['notes'])) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'I', 8.5);
            $pdf->MultiCell(0, 4, 'Catatan Dosen: ' . $evaluation['notes']);
        }
        
        // QR Code and Signature block
        $qrPayload = self::buildQrPayload($student, $stage, $evaluation);
        $qrSize = 22;
        $sigBlockWidth = 55;
        $sigX = $pdf->getLeftMargin() + $pdf->getContentWidth() - $sigBlockWidth;
        $qrX = $sigX + ($sigBlockWidth - $qrSize) / 2;
        
        $startY = $pdf->GetY() + 2;
        // Prevent orphan signature
        if ($startY + $qrSize + 18 > 285) {
            $pdf->AddPage();
            $startY = $pdf->GetY() + 4;
        }
        
        $pdf->SetXY($sigX, $startY);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($sigBlockWidth, 4, 'Jember, ' . date('d F Y'), 0, 1, 'C');
        $pdf->SetX($sigX);
        $pdf->Cell($sigBlockWidth, 4, $roleLabel . ',', 0, 1, 'C');
        
        $qrY = $pdf->GetY() + 0.5;
        $pdf->SetXY($qrX, $qrY);
        self::drawQrOnPdf($pdf, $qrPayload, $qrSize);
        
        $pdf->SetXY($sigX, $qrY + $qrSize + 0.5);
        $nameText = $evaluation['evaluator_name'] ?? '-';
        $pdf->SetFont('Arial', 'BU', 9);
        $pdf->Cell($sigBlockWidth, 4.5, $nameText, 0, 1, 'C');
        if (!empty($evaluation['nip'])) {
            $pdf->SetX($sigX);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($sigBlockWidth, 4, 'NIP. ' . $evaluation['nip'], 0, 1, 'C');
        }
        
        // Save PDF to file
        $pdf->Output($filePath, 'F');
        
        return $filePath;
    }
}

class KombiPDF extends FPDF
{
    private float $rowPadding = 0.7;
    private ?string $letterheadLogo = null;
    private array $letterheadLines = [];
    private float $headerBottomY = 0.0;
    private ?array $stageInfo = null;
    private ?array $studentInfo = null;
    private ?array $scheduleInfo = null;

    public function setStageInfo(?array $stage, ?array $student, ?array $schedule): void
    {
        $this->stageInfo = $stage;
        $this->studentInfo = $student;
        $this->scheduleInfo = $schedule;
    }

    public function getContentWidth(): float
    {
        return $this->w - $this->lMargin - $this->rMargin;
    }

    public function getLeftMargin(): float
    {
        return $this->lMargin;
    }

    public function getRightMargin(): float
    {
        return $this->rMargin;
    }

    public function setRowPadding(float $padding): void
    {
        $this->rowPadding = max(0.0, $padding);
    }

    public function setLetterhead(?string $logoPath, array $lines): void
    {
        $this->letterheadLogo = (is_string($logoPath) && is_file($logoPath)) ? $logoPath : null;
        $filtered = [];
        foreach ($lines as $line) {
            $text = trim((string) $line);
            if ($text !== '') {
                $filtered[] = $text;
            }
        }
        $this->letterheadLines = $filtered;
    }

    public function getHeaderBottom(): float
    {
        return $this->headerBottomY > 0 ? $this->headerBottomY : (float) $this->tMargin;
    }

    public function Header(): void
    {
        $topY = 12;
        $contentWidth = $this->getContentWidth();
        $logoBottom = $topY;

        if ($this->letterheadLogo && is_file($this->letterheadLogo)) {
            try {
                $this->Image($this->letterheadLogo, $this->lMargin, $topY - 2, 22);
                $logoBottom = ($topY - 2) + 22;
            } catch (\Throwable $e) {
                $this->letterheadLogo = null;
            }
        }

        if (!empty($this->letterheadLines)) {
            $this->SetXY($this->lMargin, $topY);
            $lineCount = count($this->letterheadLines);
            foreach ($this->letterheadLines as $index => $line) {
                $fontSize = 11.5;
                if ($index === 0) {
                    $fontSize = 12.5;
                }
                if ($index === $lineCount - 1) {
                    $fontSize = 13.0;
                }
                $this->SetFont('Times', 'B', $fontSize);
                $this->Cell($contentWidth, 5.2, strtoupper($line), 0, 1, 'C');
            }
        }

        $textBottom = !empty($this->letterheadLines) ? $this->GetY() : ($topY + 5);
        $bottom = max($logoBottom, $textBottom);
        $lineY = $bottom + 2;
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.7);
        $this->Line($this->lMargin, $lineY, $this->w - $this->rMargin, $lineY);
        $this->SetLineWidth(0.2);
        $this->Line($this->lMargin, $lineY + 2, $this->w - $this->rMargin, $lineY + 2);
        $this->SetY($lineY + 6);
        $this->headerBottomY = $this->GetY();

        // Render student information on every page
        if ($this->stageInfo && $this->studentInfo) {
            $this->SetY(max($this->headerBottomY, $this->GetY()));
            $this->SetFont('Arial', 'B', 11);
            $this->Cell(0, 5, strtoupper($this->stageInfo['title'] ?? ''), 0, 1, 'C');
            $this->SetFont('Arial', '', 9);
            $this->Cell(0, 4, AppSettings::getFullName() . ' (' . AppSettings::getShortName() . ')', 0, 1, 'C');
            $this->Ln(4); // Extra spacing before student info

            // Student info in a neat table-like layout
            $this->SetFont('Arial', '', 8);
            $leftCol = 45;
            $rightCol = 45;
            $gap = 10;
            
            // Row 1: Nama and NIM
            $this->Cell($leftCol, 4, 'Nama: ' . ($this->studentInfo['name'] ?? '-'), 0, 0, 'L');
            $this->Cell($gap, 4, '', 0, 0, 'C');
            $this->Cell($rightCol, 4, 'NIM: ' . ($this->studentInfo['nim'] ?? '-'), 0, 1, 'L');
            
            // Row 2: Angkatan and Tahap
            $this->Cell($leftCol, 4, 'Angkatan: ' . ($this->studentInfo['angkatan'] ?? '-'), 0, 0, 'L');
            $this->Cell($gap, 4, '', 0, 0, 'C');
            $this->Cell($rightCol, 4, 'Tahap: ' . ($this->stageInfo['label'] ?? '-'), 0, 1, 'L');
            
            // Row 3: Jadwal and Ruang (if schedule info exists)
            if ($this->scheduleInfo) {
                $this->Cell($leftCol, 4, 'Jadwal: ' . ($this->scheduleInfo['date_text'] ?? '-'), 0, 0, 'L');
                $this->Cell($gap, 4, '', 0, 0, 'C');
                $this->Cell($rightCol, 4, 'Ruang: ' . ($this->scheduleInfo['room'] ?? '-'), 0, 1, 'L');
            }
            
            $this->Ln(2);
        }
    }

    /**
     * Draw a row of cells with automatic wrapping and optional font shrinking per column.
     *
     * @param array<array{
     *     text:string,
     *     width?:float,
     *     align?:string,
     *     font?:array,
     *     border?:int,
     *     shrink_to_fit?:bool,
     *     min_font_size?:float,
     *     valign?:string
     * }> $columns
     */
    public function Row(array $columns, float $lineHeight = 7.0): void
    {
        $lineHeight = max(4.0, $lineHeight);
        $baseFont = [$this->FontFamily, $this->FontStyle, $this->FontSizePt];

        $prepared = [];
        $maxLines = 1;
        $cursor = $this->GetX();

        foreach ($columns as $column) {
            $text = (string) ($column['text'] ?? '');
            $width = $this->resolveWidth($column['width'] ?? 0.0, $cursor);
            $fontSpec = $this->resolveFontSpec($column['font'] ?? null, $baseFont);
            if (!empty($column['shrink_to_fit'])) {
                $fontSpec = $this->shrinkFontToWidth($fontSpec, $width, $text, (float) ($column['min_font_size'] ?? 8.0));
            }
            $lineCount = $this->calculateLineCount($width, $text, $fontSpec);
            $maxLines = max($maxLines, $lineCount);

            $prepared[] = [
                'text' => $text,
                'width' => $width,
                'align' => strtoupper($column['align'] ?? 'L'),
                'border' => $column['border'] ?? 1,
                'font' => $fontSpec,
                'valign' => strtoupper($column['valign'] ?? 'M'),
                'lines' => $lineCount,
            ];
            $cursor += $width;
        }

        $height = ($lineHeight * $maxLines) + (2 * $this->rowPadding);
        $this->ensurePageBreak($height);
        $y = $this->GetY();

        foreach ($prepared as $column) {
            $x = $this->GetX();
            if ($column['border']) {
                $this->Rect($x, $y, $column['width'], $height);
            }

            $columnHeight = $column['lines'] * $lineHeight;
            $availableSpace = max(0.0, $height - (2 * $this->rowPadding) - $columnHeight);
            $textY = $y + $this->rowPadding;
            $valign = $column['valign'];
            if ($valign === 'AUTO') {
                $valign = $maxLines > 1 ? 'T' : 'M';
            }
            if ($valign === 'M') {
                $textY += $availableSpace / 2;
            } elseif ($valign === 'B') {
                $textY += $availableSpace;
            }

            $this->SetXY($x, $textY);
            $this->SetFont($column['font'][0], $column['font'][1], $column['font'][2]);
            $this->MultiCell($column['width'], $lineHeight, $column['text'], 0, $column['align']);
            $this->SetXY($x + $column['width'], $y);
        }

        $this->SetY($y + $height);
        $this->SetFont($baseFont[0], $baseFont[1], $baseFont[2]);
    }

    private function ensurePageBreak(float $height): void
    {
        if ($this->GetY() + $height <= $this->PageBreakTrigger) {
            return;
        }
        $this->AddPage($this->CurOrientation, $this->CurPageSize, $this->CurRotation);
    }

    private function resolveWidth(float $width, float $currentX): float
    {
        if ($width > 0) {
            return $width;
        }
        $available = $this->w - $this->rMargin - $currentX;
        return max(5.0, $available);
    }

    private function NbLinesForCurrentFont(float $width, string $text): int
    {
        $cw = $this->CurrentFont['cw'] ?? [];
        if ($width <= 0.0) {
            $width = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($width - 2 * $this->cMargin) * 1000 / max(0.1, $this->FontSizePt);

        $text = str_replace("\r", '', $text);
        $length = strlen($text);
        if ($length > 0 && $text[$length - 1] === "\n") {
            $length--;
        }

        $sep = -1;
        $i = 0;
        $j = 0;
        $lineWidth = 0;
        $lineCount = 1;

        while ($i < $length) {
            $char = $text[$i];
            if ($char === "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $lineWidth = 0;
                $lineCount++;
                continue;
            }
            if ($char === ' ') {
                $sep = $i;
            }
            $lineWidth += $cw[$char] ?? 0;
            if ($lineWidth > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $lineWidth = 0;
                $lineCount++;
            } else {
                $i++;
            }
        }

        return $lineCount;
    }

    private function resolveFontSpec(?array $fontSpec, array $baseFont): array
    {
        return [
            $fontSpec[0] ?? $baseFont[0],
            $fontSpec[1] ?? $baseFont[1],
            isset($fontSpec[2]) ? (float) $fontSpec[2] : $baseFont[2],
        ];
    }

    private function calculateLineCount(float $width, string $text, array $fontSpec): int
    {
        $previousFont = [$this->FontFamily, $this->FontStyle, $this->FontSizePt];
        $this->SetFont($fontSpec[0], $fontSpec[1], $fontSpec[2]);
        $lineCount = $this->NbLinesForCurrentFont($width, $text);
        $this->SetFont($previousFont[0], $previousFont[1], $previousFont[2]);
        return $lineCount;
    }

    private function shrinkFontToWidth(array $fontSpec, float $width, string $text, float $minFontSize): array
    {
        $availableWidth = $width - 2 * $this->cMargin;
        if ($availableWidth <= 0) {
            return $fontSpec;
        }

        $previousFont = [$this->FontFamily, $this->FontStyle, $this->FontSizePt];
        $size = $fontSpec[2];
        $minSize = max(4.0, $minFontSize);

        $this->SetFont($fontSpec[0], $fontSpec[1], $size);
        if ($this->GetStringWidth($text) <= $availableWidth) {
            $this->SetFont($previousFont[0], $previousFont[1], $previousFont[2]);
            return $fontSpec;
        }

        while ($size > $minSize && $this->GetStringWidth($text) > $availableWidth) {
            $size -= 0.3;
            $this->SetFont($fontSpec[0], $fontSpec[1], $size);
        }

        $this->SetFont($previousFont[0], $previousFont[1], $previousFont[2]);
        $fontSpec[2] = max($size, $minSize);
        return $fontSpec;
    }

}
