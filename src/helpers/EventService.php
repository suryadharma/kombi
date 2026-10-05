<?php

require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/EmailQueueHelper.php';
require_once __DIR__ . '/PdfExporter.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../config/database.php';

class EventService
{
    private const STAGE_TO_EVENT_TYPE = [
        'sempro' => 'SEMPRO',
        'semhas' => 'SEMHAS',
        'pra-ujian' => 'PRA_UJIAN',
        'ujian' => 'UJIAN_SKRIPSI'
    ];

    private const EVENT_TYPE_TO_STAGE = [
        'SEMPRO' => 'sempro',
        'SEMHAS' => 'semhas',
        'PRA_UJIAN' => 'pra-ujian',
        'UJIAN_SKRIPSI' => 'ujian'
    ];

    private const STAGE_LABELS = [
        'sempro' => 'Seminar Proposal',
        'semhas' => 'Seminar Hasil',
        'pra-ujian' => 'Pra-Ujian Skripsi',
        'ujian' => 'Ujian Skripsi'
    ];

    private const STAGE_ASSIGNMENT_ROLES = [
        'sempro' => ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3'],
        'semhas' => ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3'],
        'pra-ujian' => ['pembimbing_1', 'pembimbing_2'],
        'ujian' => ['penguji_1', 'penguji_2', 'penguji_3']
    ];

    public static function stageLabel(string $stage): string
    {
        return self::STAGE_LABELS[$stage] ?? ucfirst($stage);
    }

    public static function toEventType(string $stage): ?string
    {
        $stage = strtolower($stage);
        return self::STAGE_TO_EVENT_TYPE[$stage] ?? null;
    }

    public static function toStage(string $eventType): ?string
    {
        return self::EVENT_TYPE_TO_STAGE[$eventType] ?? null;
    }

    /**
     * Create or update an event for the given student and stage.
     *
     * @return array{id:int,is_new:bool,type:string,stage:string}
     */
    public static function upsertEvent(
        PDO $db,
        int $studentId,
        string $stage,
        string $date,
        string $time,
        string $room,
        string $status = 'MENUNGGU'
    ): array {
        $eventType = self::toEventType($stage);
        if (!$eventType) {
            throw new InvalidArgumentException('Tahap ujian tidak dikenal.');
        }

        // Cari event terakhir untuk tahap ini (urutan terbaru)
        $select = "SELECT id, status FROM events 
                   WHERE student_id = :student_id AND type = :type 
                   ORDER BY scheduled_date DESC, id DESC 
                   LIMIT 1";
        $stmt = $db->prepare($select);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':type', $eventType);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jika ada event yang masih MENUNGGU, kita update (revisi jadwal)
        // Jika statusnya SELESAI/BATAL (misal ujian ulang), kita buat event BARU
        if ($row && $row['status'] === 'MENUNGGU') {
            $update = "UPDATE events
                       SET scheduled_date = :date,
                           scheduled_time = :time,
                           room = :room,
                           status = :status
                       WHERE id = :id";
            $updateStmt = $db->prepare($update);
            $updateStmt->bindParam(':date', $date);
            $updateStmt->bindParam(':time', $time);
            $updateStmt->bindParam(':room', $room);
            $updateStmt->bindParam(':status', $status);
            $updateStmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            return [
                'id' => (int) $row['id'],
                'is_new' => false,
                'type' => $eventType,
                'stage' => $stage
            ];
        }

        // Jika belum ada event ATAU event terakhir sudah SELESAI/BATAL -> Insert Baru
        $insert = "INSERT INTO events (student_id, type, scheduled_date, scheduled_time, room, status)
                   VALUES (:student_id, :type, :date, :time, :room, :status)";
        $insertStmt = $db->prepare($insert);
        $insertStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $insertStmt->bindParam(':type', $eventType);
        $insertStmt->bindParam(':date', $date);
        $insertStmt->bindParam(':time', $time);
        $insertStmt->bindParam(':room', $room);
        $insertStmt->bindParam(':status', $status);
        $insertStmt->execute();

        return [
            'id' => (int) $db->lastInsertId(),
            'is_new' => true,
            'type' => $eventType,
            'stage' => $stage
        ];
    }

    public static function markEventCompletedIfScoresReady(PDO $db, int $studentId, string $stage): void
    {
        $stage = strtolower($stage);
        $eventType = self::toEventType($stage);
        if (!$eventType) {
            return;
        }

        $eventStmt = $db->prepare("SELECT id, status FROM events WHERE student_id = :student_id AND type = :type ORDER BY scheduled_date DESC, scheduled_time DESC, id DESC LIMIT 1");
        $eventStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $eventStmt->bindParam(':type', $eventType);
        $eventStmt->execute();
        $event = $eventStmt->fetch(PDO::FETCH_ASSOC);
        if (!$event || ($event['status'] ?? null) !== 'MENUNGGU') {
            return;
        }

        // Bypass evaluation automatically marks event as complete
        $bypassStmt = $db->prepare("SELECT 1 FROM evaluations WHERE student_id = :student_id AND stage = :stage AND mode = 'bypass' AND final_score IS NOT NULL LIMIT 1");
        $bypassStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $bypassStmt->bindParam(':stage', $stage);
        $bypassStmt->execute();
        if ($bypassStmt->fetchColumn()) {
            self::setEventStatus($db, (int) $event['id'], 'SELESAI', (int) $studentId, $stage);
            return;
        }

        $requiredRoles = self::STAGE_ASSIGNMENT_ROLES[$stage] ?? [];
        if (empty($requiredRoles)) {
            return;
        }

        $assignStmt = $db->prepare("SELECT role, lecturer_id FROM assignments WHERE student_id = :student_id");
        $assignStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $assignStmt->execute();

        $expectedEvaluators = [];
        while ($row = $assignStmt->fetch(PDO::FETCH_ASSOC)) {
            $role = $row['role'] ?? null;
            $lecturerId = isset($row['lecturer_id']) ? (int) $row['lecturer_id'] : 0;
            if ($lecturerId > 0 && in_array($role, $requiredRoles, true)) {
                $expectedEvaluators[$lecturerId] = true;
            }
        }

        if (empty($expectedEvaluators)) {
            return;
        }

        $evalStmt = $db->prepare("SELECT DISTINCT evaluator_id FROM evaluations WHERE student_id = :student_id AND stage = :stage AND final_score IS NOT NULL AND evaluator_id IS NOT NULL");
        $evalStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $evalStmt->bindParam(':stage', $stage);
        $evalStmt->execute();
        $completedEvaluators = $evalStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($expectedEvaluators as $lecturerId => $_) {
            if (!in_array($lecturerId, $completedEvaluators)) {
                return;
            }
        }

        self::setEventStatus($db, (int) $event['id'], 'SELESAI');
        
        // Send batch/final email when all evaluators have completed
        // Only send if email is enabled
        $emailEnabled = Settings::get('email_enabled', '0');
        
        if ($emailEnabled === '1') {
            try {
                // Send individual email to each lecturer with their own scores only (Opsi 1: Privasi Penuh)
                foreach ($expectedEvaluators as $lecturerId => $_) {
                    self::queueEmailForLecturer($db, (int) $studentId, $stage, (int) $lecturerId);
                }
            } catch (Exception $e) {
                error_log("EventService::markEventCompletedIfScoresReady - Failed to send batch emails: " . $e->getMessage());
            }
        }
    }

    private static function setEventStatus(PDO $db, int $eventId, string $status): void
    {
        $update = $db->prepare("UPDATE events SET status = :status WHERE id = :id");
        $update->bindParam(':status', $status);
        $update->bindParam(':id', $eventId, PDO::PARAM_INT);
        $update->execute();
    }

    /**
     * Queue email untuk dosen yang baru saja menginput nilai
     * Dipanggil setelah score berhasil disimpan
     *
     * @param PDO $db Database connection
     * @param int $studentId Student ID
     * @param string $stage Stage (sempro, semhas, pra-ujian, ujian)
     * @param int $lecturerId Lecturer (user) ID yang menginput nilai
     */
    public static function queueEmailForLecturer(PDO $db, int $studentId, string $stage, int $lecturerId): void
    {
        // Write to a simple debug file
        $debugFile = __DIR__ . '/../../storage/logs/email_debug.log';
        $debugDir = dirname($debugFile);
        if (!is_dir($debugDir)) {
            @mkdir($debugDir, 0755, true);
        }
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - queueEmailForLecturer called: studentId=$studentId, stage=$stage, lecturerId=$lecturerId\n", FILE_APPEND);
        
        try {
            error_log("=== EventService::queueEmailForLecturer START ===");
            error_log("studentId={$studentId}, stage={$stage}, lecturerId={$lecturerId}");
            
            // Cek apakah fitur email diaktifkan
            $enabled = Settings::get('email_enabled', '0');
            error_log("email_enabled setting: " . var_export($enabled, true));
            
            if ($enabled !== '1') {
                error_log("Email is DISABLED, returning early");
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Email is DISABLED, returning\n", FILE_APPEND);
                return; // Email tidak diaktifkan
            }
            error_log("Email is ENABLED, proceeding...");
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Email is ENABLED, proceeding\n", FILE_APPEND);

            // Cek email dosen
            $lecturerQuery = "SELECT u.name, l.email
                               FROM users u
                               LEFT JOIN lecturers l ON u.id = l.user_id
                               WHERE u.id = :lecturer_id";
            $lecturerStmt = $db->prepare($lecturerQuery);
            $lecturerStmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
            $lecturerStmt->execute();
            $lecturer = $lecturerStmt->fetch(PDO::FETCH_ASSOC);

            error_log("EventService::queueEmailForLecturer - Lecturer data: " . json_encode($lecturer));

            // Skip jika email kosong
            if (!$lecturer || empty($lecturer['email'])) {
                error_log("EventService::queueEmailForLecturer - Lecturer email is EMPTY, returning");
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Lecturer email is EMPTY, returning\n", FILE_APPEND);
                return;
            }
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Lecturer email found: {$lecturer['email']}\n", FILE_APPEND);

            // Get student info
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Getting student info...\n", FILE_APPEND);
            $studentQuery = "SELECT s.*, t.title FROM students s
                              LEFT JOIN titles t ON s.id = t.student_id
                              WHERE s.id = :student_id";
            $studentStmt = $db->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $studentStmt->execute();
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Student not found, returning\n", FILE_APPEND);
                return;
            }
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Student found: {$student['name']}\n", FILE_APPEND);

            // Get event info
            $eventType = self::toEventType($stage);
            if (!$eventType) {
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Invalid event type for stage: {$stage}, returning\n", FILE_APPEND);
                return;
            }
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Event type: {$eventType}\n", FILE_APPEND);

            $eventQuery = "SELECT * FROM events
                          WHERE student_id = :student_id
                          AND type = :event_type
                          ORDER BY created_at DESC LIMIT 1";
            $eventStmt = $db->prepare($eventQuery);
            $eventStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $eventStmt->bindParam(':event_type', $eventType);
            $eventStmt->execute();
            $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

            $eventDate = $event ? date('d/m/Y', strtotime($event['scheduled_date'])) : date('d/m/Y');
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Event date: {$eventDate}\n", FILE_APPEND);

            // Get evaluation data for this lecturer
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Getting evaluation data...\n", FILE_APPEND);
            $evalQuery = "SELECT e.*, u.name as evaluator_name, u.nip, a.role as assignment_role,
                          u.id as evaluator_id, l.email as evaluator_email
                          FROM evaluations e
                          JOIN users u ON e.evaluator_id = u.id
                          JOIN assignments a ON e.evaluator_id = a.lecturer_id AND e.student_id = a.student_id
                          LEFT JOIN lecturers l ON u.id = l.user_id
                          WHERE e.student_id = :student_id
                          AND e.stage = :stage
                          AND e.evaluator_id = :lecturer_id
                          AND e.final_score IS NOT NULL";
            $evalStmt = $db->prepare($evalQuery);
            $evalStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $evalStmt->bindParam(':stage', $stage);
            $evalStmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
            $evalStmt->execute();
            $evaluation = $evalStmt->fetch(PDO::FETCH_ASSOC);

            if (!$evaluation) {
                error_log("EventService::queueEmailForLecturer - No evaluation found for lecturer {$lecturerId}");
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - No evaluation found for lecturer {$lecturerId}, returning\n", FILE_APPEND);
                return;
            }
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Evaluation found, score: {$evaluation['final_score']}\n", FILE_APPEND);

            // Fetch component scores for this evaluation (same as EvaluationExportService)
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Fetching component scores...\n", FILE_APPEND);
            $evaluationId = $evaluation['id'];
            $components = [];
            $componentScoreQuery = "SELECT ec.name, ec.weight, es.score
                                    FROM evaluation_scores es
                                    JOIN evaluation_components ec ON ec.id = es.component_id
                                    WHERE es.evaluation_id = :evaluation_id
                                    ORDER BY ec.sort_order, ec.id";
            $componentScoreStmt = $db->prepare($componentScoreQuery);
            $componentScoreStmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
            $componentScoreStmt->execute();
            $componentScores = $componentScoreStmt->fetchAll(PDO::FETCH_ASSOC);
            
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Component scores count: " . count($componentScores) . "\n", FILE_APPEND);
            
            // Build components array with calculated weighted scores
            foreach ($componentScores as $component) {
                $weight = isset($component['weight']) ? (float) $component['weight'] : 0.0;
                $score = isset($component['score']) ? (float) $component['score'] : null;
                $weightFraction = $weight > 1 ? $weight / 100 : $weight;
                $weighted = ($weightFraction > 0 && $score !== null) ? $score * $weightFraction : null;
                
                $components[] = [
                    'name' => $component['name'],
                    'weight' => $weight,
                    'score' => $score,
                ];
            }
            
            // Add components to evaluation data
            $evaluation['components'] = $components;
            
            // Calculate total score from components if not already set
            if (!isset($evaluation['total_score']) && !empty($components)) {
                $rawTotal = 0.0;
                foreach ($components as $component) {
                    $weight = isset($component['weight']) ? (float) $component['weight'] : 0.0;
                    $weightFraction = $weight > 1 ? $weight / 100 : $weight;
                    $scoreValue = isset($component['score']) ? (float) $component['score'] : null;
                    if ($scoreValue !== null && $weightFraction > 0) {
                        $rawTotal += $scoreValue * $weightFraction;
                    }
                }
                $evaluation['total_score'] = round($rawTotal, 2);
            }
            
            // Build role label (same as EvaluationExportService)
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Building role label...\n", FILE_APPEND);
            $assignmentRole = $evaluation['assignment_role'] ?? null;
            $evaluatorRole = $evaluation['evaluator_role'] ?? null;
            $roleLabel = self::formatEvaluationRoleLabel($assignmentRole, $evaluatorRole);
            $evaluation['role_label'] = $roleLabel;
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Role label: {$roleLabel}\n", FILE_APPEND);

            // Generate PDF for this lecturer's evaluation
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Generating PDF...\n", FILE_APPEND);
            $pdfPath = '';
            try {
                // Create storage directory if it doesn't exist
                $storageDir = __DIR__ . '/../storage/evaluations';
                if (!is_dir($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }
                
                // Generate unique filename
                $pdfFileName = "evaluation_{$studentId}_{$stage}_{$lecturerId}_" . time() . ".pdf";
                $pdfPath = $storageDir . '/' . $pdfFileName;
                
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - PDF path: {$pdfPath}\n", FILE_APPEND);
                
                // Generate PDF
                PdfExporter::generateSingleEvaluationPdf($student, $stage, $evaluation, $pdfPath);
                
                error_log("PDF generated: {$pdfPath}");
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - PDF generated successfully\n", FILE_APPEND);
            } catch (Exception $pdfEx) {
                error_log("EventService::queueEmailForLecturer - PDF generation failed: " . $pdfEx->getMessage());
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - PDF generation FAILED: " . $pdfEx->getMessage() . "\n", FILE_APPEND);
                // Continue without PDF
                $pdfPath = '';
            }
            
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Generating email content...\n", FILE_APPEND);

            // Generate email content
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Generating email content...\n", FILE_APPEND);
            
            try {
                $emailHelper = new EmailQueueHelper($db);
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - EmailQueueHelper instantiated\n", FILE_APPEND);
                
                $subject = $emailHelper->generateSubject($eventType, $student['name']);
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Subject generated: {$subject}\n", FILE_APPEND);
                
                $body = $emailHelper->generateBody($eventType, $student['name'], $student['nim'], $student['title'] ?? 'Judul tidak tersedia', $eventDate);
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Body generated (length: " . strlen($body) . ")\n", FILE_APPEND);
            } catch (Exception $e) {
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - EmailQueueHelper ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
                // Use fallback subject/body
                $subject = "Dokumen Penilaian - {$student['name']}";
                $body = "Berikut terlampir dokumen penilaian untuk mahasiswa {$student['name']} ({$student['nim']})";
            }

            error_log("EventService::queueEmailForLecturer - About to queue email to: {$lecturer['email']}, Subject: {$subject}");
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - About to queue email to: {$lecturer['email']}\n", FILE_APPEND);

            // Queue email for asynchronous sending instead of sending immediately
            // This improves performance by not blocking the user's request
            $emailService = new EmailService($db);
            
            $queueId = $emailService->queueEmail(
                $lecturer['email'],
                $lecturer['name'],
                $subject,
                $body,
                $pdfPath,
                $eventType,
                $studentId,
                1 // priority: 1 = high
            );

            error_log("EventService::queueEmailForLecturer - Email queued with ID: " . ($queueId ?: 'false'));
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Email queued with ID: " . ($queueId ?: 'false') . "\n", FILE_APPEND);

            if ($queueId) {
                error_log("Email queued successfully for {$lecturer['name']} ({$lecturer['email']}) - Queue ID: {$queueId}");
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ✅ Email queued successfully! Queue ID: {$queueId}\n", FILE_APPEND);
            } else {
                error_log("Failed to queue email for {$lecturer['name']} ({$lecturer['email']})");
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ❌ Failed to queue email\n", FILE_APPEND);
            }
            
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - === queueEmailForLecturer END ===\n", FILE_APPEND);

        } catch (Exception $e) {
            error_log("=== EventService::queueEmailForLecturer EXCEPTION ===");
            error_log("Message: " . $e->getMessage());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            error_log("Trace: " . $e->getTraceAsString());
        }
        error_log("=== EventService::queueEmailForLecturer END ===");
    }

    /**
     * Generate PDF for evaluation and return the file path
     */
    private static function generateEvaluationPdf(PDO $db, int $studentId, string $stage, int $eventId): ?string
    {
        try {
            // This should integrate with the existing PDF generation system
            // For now, we'll create a placeholder path that should be replaced
            // with the actual PDF generation logic from ScoreController::exportStage
            
            $pdfDir = __DIR__ . '/../storage/pdfs/evaluations/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            $filename = "evaluation_{$stage}_student_{$studentId}_event_{$eventId}_" . time() . ".pdf";
            $pdfPath = $pdfDir . $filename;

            // TODO: Integrate with existing PDF generation
            // For now, we'll return null to indicate PDF should be generated
            // The actual PDF generation should be done by calling the existing
            // export functionality from ScoreController or PdfExporter
            
            return null; // PDF generation will be handled separately

        } catch (Exception $e) {
            error_log("EventService::generateEvaluationPdf Error: " . $e->getMessage());
            return null;
        }
    }

    public static function syncPendingEvents(PDO $db, ?int $studentId = null, ?string $stage = null): void
    {
        $conditions = ["status = 'MENUNGGU'"];
        $params = [];

        if ($studentId) {
            $conditions[] = "student_id = :student_id";
            $params[':student_id'] = $studentId;
        }

        if ($stage) {
            $eventType = self::toEventType($stage);
            if ($eventType) {
                $conditions[] = "type = :type";
                $params[':type'] = $eventType;
            }
        }

        $sql = "SELECT id, student_id, type FROM events WHERE " . implode(' AND ', $conditions);
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':student_id') {
                $stmt->bindValue($key, (int) $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stageName = self::toStage($row['type']);
            if ($stageName) {
                self::markEventCompletedIfScoresReady($db, (int) $row['student_id'], $stageName);
            }
        }
    }

    /**
     * Format evaluation role label (same as EvaluationExportService)
     */
    private static function formatEvaluationRoleLabel(?string $assignmentRole, ?string $fallbackRole): string
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
}
