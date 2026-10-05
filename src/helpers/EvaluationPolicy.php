<?php

class EvaluationPolicy
{
    private const STAGE_EVENT_MAP = [
        'sempro' => 'SEMPRO',
        'semhas' => 'SEMHAS',
        'pra-ujian' => 'PRA_UJIAN',
        'ujian' => 'UJIAN_SKRIPSI'
    ];

    /**
     * Determine whether the current role may input evaluation for a given stage.
     *
     * @param PDO $db Database connection
     * @param int $studentId Student ID
     * @param string $stage Stage name (sempro, semhas, pra-ujian, ujian)
     * @param string $role User role
     * @param int|null $userId User ID (lecturer ID) - required for pra-ujian stage validation
     * @return array{0: bool, 1: ?string} [allowed, lockReason]
     */
    public static function checkStageAccess(PDO $db, int $studentId, string $stage, string $role, ?int $userId = null): array
    {
        $stage = strtolower($stage);

        if (in_array($role, ['superadmin', 'kombi'], true)) {
            return [true, null];
        }

        if ($stage === 'pra-ujian') {
            // Check if user is a pembimbing for this student
            if ($userId !== null) {
                $assignmentQuery = "SELECT role FROM assignments
                                  WHERE student_id = :student_id
                                  AND lecturer_id = :lecturer_id
                                  LIMIT 1";
                $assignmentStmt = $db->prepare($assignmentQuery);
                $assignmentStmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
                $assignmentStmt->bindValue(':lecturer_id', $userId, PDO::PARAM_INT);
                $assignmentStmt->execute();
                $assignmentRole = $assignmentStmt->fetchColumn();

                // Only pembimbing can evaluate pra-ujian
                if (!$assignmentRole || strpos($assignmentRole, 'pembimbing') === false) {
                    $message = 'Hanya Dosen Pembimbing yang dapat mengisi nilai Pra-Ujian.';
                    return [false, $message];
                }
            }

            if (!self::hasPembimbingSemhasScore($db, $studentId)) {
                $message = 'Nilai Seminar Hasil oleh pembimbing belum tersedia. Penilaian Pra-Ujian baru dapat dilakukan setelah nilai tersebut diinput.';
                return [false, $message];
            }

            return [true, null];
        }

        $eventType = self::STAGE_EVENT_MAP[$stage] ?? null;
        if ($eventType) {
            $event = self::fetchLatestEvent($db, $studentId, $eventType);
            if (!$event) {
                $label = self::stageLabel($stage);
                $message = "Jadwal {$label} belum ditetapkan oleh Kombi. Penilaian baru dapat dilakukan setelah jadwal tersedia.";
                return [false, $message];
            }

            if (!self::isEventDateReached($event)) {
                $formattedDate = self::formatEventDate($event);
                $label = self::stageLabel($stage);
                $message = "Penilaian {$label} baru dapat dilakukan pada {$formattedDate}.";
                return [false, $message];
            }
        }

        return [true, null];
    }

    public static function canRoleEditStage(string $stage, string $role): bool
    {
        $stage = strtolower($stage);

        if ($role === 'superadmin') {
            return true;
        }

        $editableRoles = [
            'sempro' => ['dosen_pembimbing', 'dosen_penguji'],
            'semhas' => ['dosen_pembimbing', 'dosen_penguji'],
            'pra-ujian' => ['dosen_pembimbing'],
            'ujian' => ['dosen_penguji'],
        ];

        return isset($editableRoles[$stage]) && in_array($role, $editableRoles[$stage], true);
    }

    /**
     * Check if an evaluation can be edited based on edit window and lock status
     *
     * @param PDO $db Database connection
     * @param int $evaluationId Evaluation ID to check
     * @param string $role User role
     * @return array{0: bool, 1: ?string} [canEdit, lockReason]
     */
    public static function canEditEvaluation(PDO $db, int $evaluationId, string $role): array
    {
        // Superadmin and kombi can always edit
        if (in_array($role, ['superadmin', 'kombi'], true)) {
            return [true, null];
        }

        // Get evaluation data with lock status and created_at timestamp
        $query = "
            SELECT e.locked_at, e.created_at, e.updated_at,
                   s.name as student_name, e.stage
            FROM evaluations e
            JOIN students s ON s.id = e.student_id
            WHERE e.id = :evaluation_id
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
        $stmt->execute();
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evaluation) {
            return [false, 'Evaluasi tidak ditemukan.'];
        }

        // Check if evaluation is manually locked
        if ($evaluation['locked_at'] !== null) {
            return [false, 'Nilai ini telah dikunci secara manual dan tidak dapat diedit.'];
        }

        // Check edit window
        $editWindowHours = Settings::getEvaluationEditWindowHours();
        $createdAt = strtotime($evaluation['created_at']);
        $now = time();
        $hoursSinceCreation = ($now - $createdAt) / 3600;

        if ($hoursSinceCreation > $editWindowHours) {
            $stageLabel = self::stageLabel($evaluation['stage']);
            return [false, "Jendela edit untuk nilai {$stageLabel} telah berakhir ({$editWindowHours} jam). Nilai telah terkunci otomatis."];
        }

        return [true, null];
    }

    /**
     * Check if an evaluation is locked (either manually or by edit window)
     *
     * @param PDO $db Database connection
     * @param int $evaluationId Evaluation ID to check
     * @return bool True if evaluation is locked
     */
    public static function isEvaluationLocked(PDO $db, int $evaluationId): bool
    {
        $query = "
            SELECT e.locked_at, e.created_at
            FROM evaluations e
            WHERE e.id = :evaluation_id
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
        $stmt->execute();
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evaluation) {
            return true; // Not found = locked
        }

        // Check manual lock
        if ($evaluation['locked_at'] !== null) {
            return true;
        }

        // Check edit window
        $editWindowHours = Settings::getEvaluationEditWindowHours();
        $createdAt = strtotime($evaluation['created_at']);
        $now = time();
        $hoursSinceCreation = ($now - $createdAt) / 3600;

        return $hoursSinceCreation > $editWindowHours;
    }

    /**
     * Get remaining edit window time in hours
     *
     * @param PDO $db Database connection
     * @param int $evaluationId Evaluation ID to check
     * @return float|null Remaining hours, or null if locked
     */
    public static function getRemainingEditWindow(PDO $db, int $evaluationId): ?float
    {
        $query = "
            SELECT e.locked_at, e.created_at
            FROM evaluations e
            WHERE e.id = :evaluation_id
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
        $stmt->execute();
        $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evaluation || $evaluation['locked_at'] !== null) {
            return null;
        }

        $editWindowHours = Settings::getEvaluationEditWindowHours();
        $createdAt = strtotime($evaluation['created_at']);
        $now = time();
        $hoursSinceCreation = ($now - $createdAt) / 3600;
        $remaining = $editWindowHours - $hoursSinceCreation;

        return $remaining > 0 ? $remaining : 0;
    }

    public static function stageLabel(string $stage): string
    {
        $labels = [
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'pra-ujian' => 'Pra-Ujian Skripsi',
            'ujian' => 'Ujian Skripsi'
        ];

        return $labels[$stage] ?? ucfirst($stage);
    }

    private static function fetchLatestEvent(PDO $db, int $studentId, string $type): ?array
    {
        $query = "
            SELECT scheduled_date, scheduled_time
            FROM events
            WHERE student_id = :student_id AND type = :type
            ORDER BY scheduled_date DESC, scheduled_time DESC
            LIMIT 1
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $event ?: null;
    }

    private static function hasPembimbingSemhasScore(PDO $db, int $studentId): bool
    {
        $query = "
            SELECT 1
            FROM evaluations
            WHERE student_id = :student_id
              AND stage = 'semhas'
              AND evaluator_role = 'dosen_pembimbing'
              AND final_score IS NOT NULL
            LIMIT 1
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }

    private static function isEventDateReached(array $event): bool
    {
        $date = $event['scheduled_date'] ?? null;
        if (!$date) {
            return false;
        }

        $time = $event['scheduled_time'] ?? '00:00:00';
        $dateTimeString = trim($date . ' ' . $time);

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTimeString) ?:
                    DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateTimeString) ?:
                    DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if (!$dateTime) {
            return false;
        }

        $now = new DateTimeImmutable('now');
        return $dateTime <= $now;
    }

    private static function formatEventDate(array $event): string
    {
        $date = $event['scheduled_date'] ?? null;
        $time = $event['scheduled_time'] ?? null;

        if (!$date) {
            return 'jadwal yang ditetapkan';
        }

        $dateTimeString = trim($date . ' ' . ($time ?: '00:00:00'));
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTimeString) ?:
                    DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateTimeString) ?:
                    DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if (!$dateTime) {
            return date('d M Y', strtotime($date));
        }

        return $dateTime->format($time ? 'd M Y H:i' : 'd M Y');
    }
}
