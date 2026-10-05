<?php

class EvaluationExportService
{
    private static array $allowedStages = ['sempro', 'semhas', 'pra-ujian', 'ujian'];

    private static array $eventTypeMap = [
        'sempro' => 'SEMPRO',
        'semhas' => 'SEMHAS',
        'pra-ujian' => 'PRA_UJIAN',
        'ujian' => 'UJIAN_SKRIPSI'
    ];

    public static function prepare(int $studentId, string $stageCode, ?int $evaluatorId = null): array
    {
        $stageCode = strtolower($stageCode);
        if (!in_array($stageCode, self::$allowedStages, true)) {
            throw new InvalidArgumentException('Tahap tidak dikenali.');
        }

        if (!class_exists('Database')) {
            $dbPath = __DIR__ . '/../config/database.php';
            if (file_exists($dbPath)) {
                require_once $dbPath;
            }
        }

        $database = new Database();
        $db = $database->getConnection();

        $studentStmt = $db->prepare("
            SELECT s.id,
                   s.nim,
                   s.name,
                   s.angkatan,
                   s.status,
                   (
                        SELECT t.title
                        FROM titles t
                        WHERE t.student_id = s.id
                          AND t.status = 'DITERIMA'
                        ORDER BY (t.verified_at IS NOT NULL) DESC,
                                 t.verified_at DESC,
                                 t.submitted_at DESC,
                                 t.id DESC
                        LIMIT 1
                   ) AS title
            FROM students s
            WHERE s.id = :id
        ");
        $studentStmt->execute([':id' => $studentId]);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            throw new RuntimeException('Mahasiswa tidak ditemukan.');
        }

        $assignmentStmt = $db->prepare("SELECT lecturer_id, role FROM assignments WHERE student_id = :student_id");
        $assignmentStmt->execute([':student_id' => $studentId]);
        $currentAssignments = [];
        while ($assignment = $assignmentStmt->fetch(PDO::FETCH_ASSOC)) {
            $lecturerId = isset($assignment['lecturer_id']) ? (int) $assignment['lecturer_id'] : 0;
            if ($lecturerId > 0) {
                $currentAssignments[$lecturerId] = [
                    'role' => $assignment['role'],
                    'is_replacement' => false
                ];
            }
        }

        $historyFormer = [];
        $historyReplacements = [];
        $historyStmt = $db->prepare("SELECT lecturer_id_old, lecturer_id_new, role
                                      FROM assignment_history
                                      WHERE student_id = :student_id
                                      ORDER BY effective_date DESC, created_at DESC");
        $historyStmt->execute([':student_id' => $studentId]);
        while ($history = $historyStmt->fetch(PDO::FETCH_ASSOC)) {
            $roleKey = $history['role'] ?? null;
            if ($roleKey === null) {
                continue;
            }
            $oldId = isset($history['lecturer_id_old']) ? (int) $history['lecturer_id_old'] : 0;
            $newId = isset($history['lecturer_id_new']) ? (int) $history['lecturer_id_new'] : 0;
            if ($oldId > 0 && !isset($historyFormer[$oldId])) {
                $historyFormer[$oldId] = $roleKey;
            }
            if ($newId > 0 && !isset($historyReplacements[$newId])) {
                $historyReplacements[$newId] = $roleKey;
                if (isset($currentAssignments[$newId])) {
                    $currentAssignments[$newId]['is_replacement'] = true;
                }
            }
        }

        $componentStmt = $db->prepare("SELECT name, weight
                                        FROM evaluation_components
                                        WHERE stage = :stage
                                        ORDER BY sort_order ASC, id ASC");
        $componentStmt->execute([':stage' => $stageCode]);
        $defaultComponents = $componentStmt->fetchAll(PDO::FETCH_ASSOC);

        $evaluationQuery = "SELECT e.id, e.final_score, e.total_score, e.mode, e.stage, e.evaluator_role, e.evaluator_id, e.updated_at, e.notes,
                                   u.name AS evaluator_name, u.nip,
                                   a.role AS assignment_role
                            FROM evaluations e
                            JOIN users u ON e.evaluator_id = u.id
                            LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id
                            WHERE e.student_id = :student_id AND e.stage = :stage
                            ORDER BY a.role IS NULL, a.role, u.name";
        $evaluationStmt = $db->prepare($evaluationQuery);
        $evaluationStmt->execute([
            ':student_id' => $studentId,
            ':stage' => $stageCode
        ]);
        $evaluations = $evaluationStmt->fetchAll(PDO::FETCH_ASSOC);
        $evaluationIds = array_column($evaluations, 'id');

        $targetEvaluatorId = ($evaluatorId !== null && $evaluatorId > 0) ? (int) $evaluatorId : null;
        if ($targetEvaluatorId !== null) {
            $evaluations = array_values(array_filter($evaluations, function ($evaluation) use ($targetEvaluatorId) {
                return isset($evaluation['evaluator_id']) && (int) $evaluation['evaluator_id'] === $targetEvaluatorId;
            }));
        }

        if (empty($evaluations)) {
            throw new RuntimeException('Data penilaian tidak ditemukan untuk dosen yang dipilih.');
        }

        $componentScoresMap = [];
        if (!empty($evaluationIds)) {
            $placeholders = implode(',', array_fill(0, count($evaluationIds), '?'));
            $componentScoreQuery = "SELECT es.evaluation_id, ec.name, ec.weight, es.score
                                     FROM evaluation_scores es
                                     JOIN evaluation_components ec ON ec.id = es.component_id
                                     WHERE es.evaluation_id IN ($placeholders)
                                     ORDER BY ec.sort_order, ec.id";
            $componentScoreStmt = $db->prepare($componentScoreQuery);
            $componentScoreStmt->execute($evaluationIds);
            $componentScores = $componentScoreStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($componentScores as $component) {
                $evaluationId = $component['evaluation_id'];
                if (!isset($componentScoresMap[$evaluationId])) {
                    $componentScoresMap[$evaluationId] = [];
                }
                $componentScoresMap[$evaluationId][] = [
                    'name' => $component['name'],
                    'weight' => $component['weight'],
                    'score' => $component['score']
                ];
            }
        }

        $evaluationData = [];
        foreach ($evaluations as $evaluation) {
            $assignmentRole = $evaluation['assignment_role'] ?? null;
            $assignmentStatus = 'unassigned';
            $lecturerId = isset($evaluation['evaluator_id']) ? (int) $evaluation['evaluator_id'] : 0;
            if ($lecturerId > 0) {
                if (isset($currentAssignments[$lecturerId])) {
                    $assignmentRole = $currentAssignments[$lecturerId]['role'];
                    $assignmentStatus = $currentAssignments[$lecturerId]['is_replacement'] ? 'replacement' : 'current';
                } elseif (isset($historyFormer[$lecturerId])) {
                    $assignmentRole = $historyFormer[$lecturerId];
                    $assignmentStatus = 'former';
                } elseif (isset($historyReplacements[$lecturerId])) {
                    $assignmentRole = $historyReplacements[$lecturerId];
                    $assignmentStatus = 'history';
                }
            }
            $statusLabel = self::formatAssignmentStatusLabel($assignmentStatus);

            $componentsForEval = $componentScoresMap[$evaluation['id']] ?? [];
            $rawTotal = null;
            if (!empty($componentsForEval)) {
                $rawTotal = 0.0;
                foreach ($componentsForEval as $component) {
                    $weight = isset($component['weight']) ? (float) $component['weight'] : 0.0;
                    $weightFraction = $weight > 1 ? $weight / 100 : $weight;
                    $scoreValue = isset($component['score']) ? (float) $component['score'] : null;
                    if ($scoreValue !== null && $weightFraction > 0) {
                        $rawTotal += $scoreValue * $weightFraction;
                    }
                }
                $rawTotal = round($rawTotal, 2);
            }

            $roleLabel = self::formatAssignmentRoleLabel($assignmentRole, $evaluation['evaluator_role']);
            if ($statusLabel && $assignmentStatus !== 'current') {
                $roleLabel .= ' (' . $statusLabel . ')';
            }

            $evaluationData[] = [
                'id' => $evaluation['id'],
                'evaluator_id' => $lecturerId,
                'final_score' => $evaluation['final_score'],
                'total_score' => $rawTotal !== null ? $rawTotal : $evaluation['total_score'],
                'mode' => $evaluation['mode'],
                'evaluator_role' => $evaluation['evaluator_role'],
                'assignment_role' => $assignmentRole,
                'role_label' => $roleLabel,
                'assignment_status' => $assignmentStatus,
                'assignment_status_label' => $statusLabel,
                'evaluator_name' => $evaluation['evaluator_name'],
                'nip' => $evaluation['nip'],
                'updated_at' => $evaluation['updated_at'],
                'notes' => $evaluation['notes'],
                'components' => $componentsForEval,
            ];
        }

        $scheduleData = null;
        if (isset(self::$eventTypeMap[$stageCode])) {
            $eventStmt = $db->prepare("SELECT scheduled_date, scheduled_time, room
                                         FROM events
                                         WHERE student_id = :student_id
                                           AND type = :type
                                           AND status != 'BATAL'
                                         ORDER BY scheduled_date DESC, scheduled_time DESC
                                         LIMIT 1");
            $eventStmt->execute([
                ':student_id' => $studentId,
                ':type' => self::$eventTypeMap[$stageCode]
            ]);
            $schedule = $eventStmt->fetch(PDO::FETCH_ASSOC);
            if ($schedule) {
                $dateText = '-';
                if (!empty($schedule['scheduled_date'])) {
                    $dateText = date('d M Y', strtotime($schedule['scheduled_date']));
                }
                if (!empty($schedule['scheduled_time'])) {
                    $timeText = substr($schedule['scheduled_time'], 0, 5);
                    $dateText .= ' ' . $timeText . ' WIB';
                }
                $scheduleData = [
                    'date_text' => $dateText,
                    'room' => $schedule['room'] ?? '-'
                ];
            }
        }

        return [
            'student' => $student,
            'evaluations' => $evaluationData,
            'components' => $defaultComponents,
            'schedule' => $scheduleData
        ];
    }

    private static function formatAssignmentRoleLabel(?string $assignmentRole, ?string $fallbackRole): string
    {
        $assignmentLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Penguji Ketua',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2',
        ];

        if ($assignmentRole && isset($assignmentLabels[$assignmentRole])) {
            return $assignmentLabels[$assignmentRole];
        }

        switch ($fallbackRole) {
            case 'dosen_pembimbing':
                return 'Pembimbing';
            case 'dosen_penguji':
                return 'Penguji';
            case 'kombi':
                return 'Kombi';
            case 'superadmin':
                return 'Superadmin';
            case 'penguji_eksternal':
                return 'Penguji Eksternal';
            default:
                return strtoupper($assignmentRole ?? $fallbackRole ?? '-');
        }
    }

    private static function formatAssignmentStatusLabel(?string $status): ?string
    {
        switch ($status) {
            case 'replacement':
                return 'Pengganti';
            case 'former':
            case 'history':
                return 'Riwayat';
            case 'current':
                return 'Aktif';
            case 'unassigned':
                return 'Tanpa Penetapan';
            default:
                return null;
        }
    }
}
