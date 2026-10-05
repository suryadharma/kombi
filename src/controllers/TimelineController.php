<?php

class TimelineController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        if ($this->getUserRole() !== 'mahasiswa') {
            $this->redirect('/dashboard');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $studentId = $this->resolveStudentId($db, (int)($_SESSION['user_id'] ?? 0));

        if (!$studentId) {
            $this->render('timeline/index', ['timeline' => [], 'error' => 'Data mahasiswa tidak ditemukan.']);
            return;
        }

        $titleData = $this->fetchLatestTitle($db, $studentId);
        $assignments = $this->fetchAssignments($db, $studentId);
        $events = $this->fetchEvents($db, $studentId);
        $evaluations = $this->fetchEvaluations($db, $studentId);

        $timeline = $this->buildTimeline($titleData, $assignments, $events, $evaluations);

        // Calculate final score if enabled using ReportService
        $finalScoreData = null;
        if (Settings::isFinalScoreVisible()) {
            try {
                require_once __DIR__ . '/../services/ReportService.php';
                $reportService = new ReportService($db);
                $finalScoreResult = $reportService->getFinalScoreData($studentId);
                
                if (isset($finalScoreResult['finalData'])) {
                    $finalScoreData = [
                        'final_score' => $finalScoreResult['finalData']['value'] ?? null,
                        'letter_grade' => $finalScoreResult['finalData']['letter'] ?? null,
                        'has_bypass' => $finalScoreResult['finalData']['is_bypass'] ?? false
                    ];
                }
            } catch (Exception $e) {
                // If calculation fails, just set to null
                $finalScoreData = null;
            }
        }

        $this->render('timeline/index', [
            'timeline' => $timeline,
            'titleData' => $titleData,
            'assignments' => $assignments,
            'events' => $events,
            'evaluationTimestamps' => $evaluations,
            'scoreVisibility' => Settings::getScoreVisibilityConfig(),
            'studentId' => $studentId,
            'finalScoreData' => $finalScoreData
        ]);
    }

    public function scoreDetail($params)
    {
        $this->requireAuth();
        if ($this->getUserRole() !== 'mahasiswa') {
            $this->redirect('/dashboard');
            return;
        }

        $stageParamRaw = $params['stage'] ?? $params['id'] ?? ($params[0] ?? null);
        $stageParam = strtolower((string)$stageParamRaw);
        $stageLabels = [
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'pra-ujian' => 'Pra-Ujian Skripsi',
            'ujian' => 'Ujian Skripsi'
        ];

        if (!isset($stageLabels[$stageParam])) {
            $this->redirect('/timeline');
            return;
        }

        if (!Settings::isScoreVisibleForStage($stageParam)) {
            $this->render('timeline/score_detail', [
                'error' => 'Akses nilai untuk tahap tersebut belum diaktifkan.',
                'stageLabel' => $stageLabels[$stageParam],
                'stageCode' => $stageParam
            ]);
            return;
        }

        $database = new Database();
        $db = $database->getConnection();
        $studentId = $this->resolveStudentId($db, (int)($_SESSION['user_id'] ?? 0));
        if (!$studentId) {
            $this->render('timeline/score_detail', [
                'error' => 'Data mahasiswa tidak ditemukan.',
                'stageLabel' => $stageLabels[$stageParam],
                'stageCode' => $stageParam
            ]);
            return;
        }

        try {
            $scoreData = EvaluationExportService::prepare($studentId, $stageParam, null);
            $this->render('timeline/score_detail', [
                'stageLabel' => $stageLabels[$stageParam],
                'stageCode' => $stageParam,
                'scoreData' => $scoreData,
                'downloadUrl' => "/scores/export/{$studentId}/" . rawurlencode($stageParam)
            ]);
        } catch (Exception $e) {
            $this->render('timeline/score_detail', [
                'stageLabel' => $stageLabels[$stageParam],
                'stageCode' => $stageParam,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function fetchLatestTitle(PDO $db, int $studentId): ?array {
        $query = "SELECT * FROM titles WHERE student_id = :student_id ORDER BY submitted_at DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchAssignments(PDO $db, int $studentId): array {
        $query = "SELECT role, created_at FROM assignments WHERE student_id = :student_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['role']] = $row['created_at'];
        }
        return $result;
    }

    private function fetchEvents(PDO $db, int $studentId): array {
        $query = "SELECT * FROM events WHERE student_id = :student_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[$row['type']] = $row;
        }
        return $events;
    }

    private function fetchEvaluations(PDO $db, int $studentId): array {
        $query = "SELECT stage, MAX(updated_at) AS updated_at 
                  FROM evaluations 
                  WHERE student_id = :student_id
                  GROUP BY stage";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $evaluations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $evaluations[$row['stage']] = $row['updated_at'];
        }
        return $evaluations;
    }

    private function buildTimeline(?array $title, array $assignments, array $events, array $evaluations): array {
        $timeline = [];

        $timeline[] = [
            'label' => 'Pengajuan Judul',
            'status' => $title ? 'Selesai' : 'Belum',
            'timestamp' => $title['submitted_at'] ?? null,
            'description' => $title ? 'Judul diajukan dan menunggu verifikasi.' : 'Belum ada pengajuan judul.',
            'stage_code' => null
        ];

        $timeline[] = [
            'label' => 'Verifikasi Judul',
            'status' => $title && $title['status'] !== 'MENUNGGU' ? 'Selesai' : 'Menunggu',
            'timestamp' => $title['verified_at'] ?? null,
            'description' => $title ? 'Status: ' . $title['status'] : 'Menunggu verifikasi Kombi.',
            'stage_code' => null
        ];

        $timeline[] = [
            'label' => 'Penunjukan Pembimbing',
            'status' => isset($assignments['pembimbing_1']) ? 'Selesai' : 'Menunggu',
            'timestamp' => $assignments['pembimbing_1'] ?? null,
            'description' => isset($assignments['pembimbing_1']) ? 'Pembimbing telah ditetapkan.' : 'Menunggu penetapan pembimbing.',
            'stage_code' => null
        ];

        $timeline[] = [
            'label' => 'Penunjukan Penguji',
            'status' => isset($assignments['penguji_1']) ? 'Selesai' : 'Menunggu',
            'timestamp' => $assignments['penguji_1'] ?? null,
            'description' => isset($assignments['penguji_1']) ? 'Penguji telah ditetapkan.' : 'Menunggu penetapan penguji.',
            'stage_code' => null
        ];

        $timeline[] = $this->buildStageItem('Seminar Proposal', 'sempro', $events, $evaluations, 'SEMPRO');
        $timeline[] = $this->buildStageItem('Seminar Hasil', 'semhas', $events, $evaluations, 'SEMHAS');
        $timeline[] = $this->buildStageItem('Pra-Ujian', 'pra-ujian', $events, $evaluations, 'PRA_UJIAN');
        $timeline[] = $this->buildStageItem('Ujian Skripsi', 'ujian', $events, $evaluations, 'UJIAN_SKRIPSI');

        return $timeline;
    }

    private function buildStageItem(string $label, string $stageCode, array $events, array $evaluations, string $eventKey): array {
        $event = $events[$eventKey] ?? null;
        $evaluationDate = $evaluations[$stageCode] ?? null;

        $status = 'Menunggu';
        if ($evaluationDate) {
            $status = 'Selesai';
        } elseif ($event) {
            $scheduled = strtotime($event['scheduled_date'] . ' ' . $event['scheduled_time']);
            $status = $scheduled <= time() ? 'Proses' : 'Terjadwal';
        }

        return [
            'label' => $label,
            'status' => $status,
            'timestamp' => $evaluationDate ?? ($event['scheduled_date'] ?? null),
            'description' => $event ? 'Jadwal: ' . date('d M Y H:i', strtotime($event['scheduled_date'] . ' ' . $event['scheduled_time'])) : 'Menunggu penjadwalan.',
            'stage_code' => $stageCode
        ];
    }

    private function resolveStudentId(PDO $db, int $userId): ?int
    {
        if ($userId <= 0) {
            return null;
        }
        $studentQuery = "SELECT id FROM students WHERE user_id = :user_id";
        $studentStmt = $db->prepare($studentQuery);
        $studentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $studentStmt->execute();
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        return $student ? (int)$student['id'] : null;
    }
}
