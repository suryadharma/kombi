<?php

/**
 * EmailQueueHelper - Helper class for managing email queue operations
 *
 * This class provides helper functions to queue emails when evaluations are completed.
 */

class EmailQueueHelper
{
    private $db;
    private $emailService;

    public function __construct($db = null)
    {
        $this->db = $db ?? (new Database())->getConnection();
        $this->emailService = new EmailService($this->db);
    }

    /**
     * Generate email subject
     */
    public function generateSubject(string $eventType, string $studentName): string
    {
        $eventLabels = [
            'SEMPRO' => 'Seminar Proposal',
            'SEMHAS' => 'Seminar Hasil',
            'PRA_UJIAN' => 'Pra-Ujian Skripsi',
            'UJIAN_SKRIPSI' => 'Ujian Skripsi'
        ];

        $eventLabel = $eventLabels[$eventType] ?? $eventType;
        return "Dokumen Penilaian {$eventLabel} - {$studentName}";
    }

    /**
     * Generate email body
     */
    public function generateBody(string $eventType, string $studentName, string $studentNim, string $title, string $eventDate): string
    {
        $eventLabels = [
            'SEMPRO' => 'Seminar Proposal',
            'SEMHAS' => 'Seminar Hasil',
            'PRA_UJIAN' => 'Pra-Ujian Skripsi',
            'UJIAN_SKRIPSI' => 'Ujian Skripsi'
        ];

        $eventLabel = $eventLabels[$eventType] ?? $eventType;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #3498db; }
                .pdf-notice { background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Notifikasi Penilaian {$eventLabel}</h2>
                </div>
                <div class='content'>
                    <p>Yth. Bapak/Ibu Dosen,</p>
                    <p>Terima kasih telah menginput nilai {$eventLabel} untuk mahasiswa berikut:</p>
                    
                    <div class='info-box'>
                        <strong>Nama Mahasiswa:</strong> {$studentName}<br>
                        <strong>NIM:</strong> {$studentNim}<br>
                        <strong>Judul Skripsi:</strong> {$title}<br>
                        <strong>Tanggal Pelaksanaan:</strong> {$eventDate}
                    </div>
                    
                    <div class='pdf-notice'>
                        <strong>Informasi:</strong><br>
                        Dokumen PDF berisi formulir penilaian dapat diunduh melalui sistem Kombi.
                    </div>
                    
                    <p>Dimohon untuk memeriksa kelengkapan dan kebenaran data. Jika terdapat kesalahan, silakan hubungi admin.</p>
                    
                    <p>Terima kasih atas perhatian dan kerjasama Bapak/Ibu.</p>
                </div>
                <div class='footer'>
                    <p>Email ini dikirim secara otomatis oleh Sistem Kombi</p>
                    <p>Jangan membalas email ini. Untuk pertanyaan, hubungi admin fakultas.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Queue evaluation emails to all related lecturers
     * 
     * @param int $studentId Student ID
     * @param string $eventType Event type (SEMPRO, SEMHAS, PRA_UJIAN, UJIAN_SKRIPSI)
     * @param string $pdfPath Path to the generated PDF
     * @return array ['queued' => int, 'errors' => array]
     */
    public function queueEvaluationEmails(int $studentId, string $eventType, string $pdfPath): array
    {
        $result = [
            'queued' => 0,
            'errors' => []
        ];

        // Check if email is enabled
        if (!$this->emailService->isEnabled()) {
            $result['errors'][] = 'Email feature is disabled';
            return $result;
        }

        try {
            // Get student information
            $studentQuery = "SELECT s.*, t.title FROM students s 
                            LEFT JOIN titles t ON s.id = t.student_id 
                            WHERE s.id = :student_id";
            $stmt = $this->db->prepare($studentQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $result['errors'][] = 'Student not found';
                return $result;
            }

            // Get event information
            $eventQuery = "SELECT * FROM events 
                          WHERE student_id = :student_id 
                          AND type = :event_type 
                          ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($eventQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':event_type', $eventType);
            $stmt->execute();
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            $eventDate = $event ? date('d/m/Y', strtotime($event['scheduled_date'])) : date('d/m/Y');

            // Get assigned lecturers for this student
            $lecturersQuery = "SELECT u.id, u.name, l.email, a.role 
                              FROM assignments a
                              JOIN users u ON a.lecturer_id = u.id
                              LEFT JOIN lecturers l ON u.id = l.user_id
                              WHERE a.student_id = :student_id
                              AND l.email IS NOT NULL 
                              AND l.email != ''";
            $stmt = $this->db->prepare($lecturersQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($lecturers)) {
                $result['errors'][] = 'No lecturers with email found for this student';
                return $result;
            }

            // Generate email content
            $subject = $this->emailService->generateEvaluationSubject($eventType, $student['name']);
            $body = $this->emailService->generateEvaluationEmailBody(
                $eventType,
                $student['name'],
                $student['nim'],
                $student['title'] ?? 'Judul tidak tersedia',
                $eventDate
            );

            // Queue email for each lecturer
            foreach ($lecturers as $lecturer) {
                $queueId = $this->emailService->queueEmail(
                    $lecturer['email'],
                    $lecturer['name'],
                    $subject,
                    $body,
                    $pdfPath,
                    $eventType,
                    $studentId,
                    0 // normal priority
                );

                if ($queueId) {
                    $result['queued']++;
                } else {
                    $result['errors'][] = "Failed to queue email for {$lecturer['name']} ({$lecturer['email']})";
                }
            }

            // Check if CC to student is enabled
            $ccEnabled = Settings::get('email_cc_enabled', '0') === '1';
            if ($ccEnabled) {
                // Get student email from users table
                $userQuery = "SELECT email FROM users WHERE id IN (SELECT user_id FROM students WHERE id = :student_id)";
                $stmt = $this->db->prepare($userQuery);
                $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && !empty($user['email'])) {
                    $studentSubject = "[CC] " . $subject;
                    $studentBody = $this->generateStudentEmailBody($eventType, $student['name'], $student['nim'], $student['title'] ?? '', $eventDate);

                    $this->emailService->queueEmail(
                        $user['email'],
                        $student['name'],
                        $studentSubject,
                        $studentBody,
                        $pdfPath,
                        $eventType,
                        $studentId,
                        0
                    );
                    $result['queued']++;
                }
            }

        } catch (Exception $e) {
            $result['errors'][] = "Error: " . $e->getMessage();
            error_log("EmailQueueHelper::queueEvaluationEmails Error: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Generate email body for student (CC)
     */
    private function generateStudentEmailBody(string $eventType, string $studentName, string $studentNim, string $title, string $eventDate): string
    {
        $eventLabels = [
            'SEMPRO' => 'Seminar Proposal',
            'SEMHAS' => 'Seminar Hasil',
            'PRA_UJIAN' => 'Pra-Ujian Skripsi',
            'UJIAN_SKRIPSI' => 'Ujian Skripsi'
        ];

        $eventLabel = $eventLabels[$eventType] ?? $eventType;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #27ae60; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
                .info-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #27ae60; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📋 Informasi {$eventLabel}</h2>
                </div>
                <div class='content'>
                    <p>Yth. {$studentName},</p>
                    <p>Berikut adalah informasi mengenai {$eventLabel} Anda:</p>
                    
                    <div class='info-box'>
                        <strong>Nama:</strong> {$studentName}<br>
                        <strong>NIM:</strong> {$studentNim}<br>
                        <strong>Judul Skripsi:</strong> {$title}<br>
                        <strong>Tanggal Pelaksanaan:</strong> {$eventDate}
                    </div>
                    
                    <p>Dokumen PDF berisi formulir penilaian dari dosen telah dikirimkan ke email dosen pembimbing dan penguji Anda.</p>
                    
                    <p>Terima kasih.</p>
                </div>
                <div class='footer'>
                    <p>Email ini dikirim secara otomatis oleh <?= htmlspecialchars(AppSettings::getName()) ?></p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Queue email for a specific lecturer
     * 
     * @param int $studentId Student ID
     * @param int $lecturerId Lecturer (user) ID
     * @param string $eventType Event type
     * @param string $pdfPath Path to PDF
     * @return bool Success status
     */
    public function queueLecturerEmail(int $studentId, int $lecturerId, string $eventType, string $pdfPath): bool
    {
        try {
            // Get student information
            $studentQuery = "SELECT s.*, t.title FROM students s 
                            LEFT JOIN titles t ON s.id = t.student_id 
                            WHERE s.id = :student_id";
            $stmt = $this->db->prepare($studentQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                return false;
            }

            // Get lecturer information
            $lecturerQuery = "SELECT u.name, l.email 
                             FROM users u 
                             LEFT JOIN lecturers l ON u.id = l.user_id 
                             WHERE u.id = :lecturer_id";
            $stmt = $this->db->prepare($lecturerQuery);
            $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
            $stmt->execute();
            $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lecturer || empty($lecturer['email'])) {
                return false;
            }

            // Get event information
            $eventQuery = "SELECT * FROM events 
                          WHERE student_id = :student_id 
                          AND type = :event_type 
                          ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->db->prepare($eventQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':event_type', $eventType);
            $stmt->execute();
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            $eventDate = $event ? date('d/m/Y', strtotime($event['scheduled_date'])) : date('d/m/Y');

            // Generate email content
            $subject = $this->emailService->generateEvaluationSubject($eventType, $student['name']);
            $body = $this->emailService->generateEvaluationEmailBody(
                $eventType,
                $student['name'],
                $student['nim'],
                $student['title'] ?? 'Judul tidak tersedia',
                $eventDate
            );

            // Queue email
            $queueId = $this->emailService->queueEmail(
                $lecturer['email'],
                $lecturer['name'],
                $subject,
                $body,
                $pdfPath,
                $eventType,
                $studentId,
                0
            );

            return $queueId !== false;

        } catch (Exception $e) {
            error_log("EmailQueueHelper::queueLecturerEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if all lecturers for a student have completed their evaluations
     * 
     * @param int $studentId Student ID
     * @param string $eventType Event type
     * @return bool True if all evaluations are complete
     */
    public function areEvaluationsComplete(int $studentId, string $eventType): bool
    {
        try {
            // Get assigned lecturers count
            $countQuery = "SELECT COUNT(*) FROM assignments WHERE student_id = :student_id";
            $stmt = $this->db->prepare($countQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $assignedCount = $stmt->fetchColumn();

            // Get completed evaluations count
            $evalQuery = "SELECT COUNT(*) FROM evaluations 
                         WHERE student_id = :student_id 
                         AND stage = :event_type";
            $stmt = $this->db->prepare($evalQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':event_type', $eventType);
            $stmt->execute();
            $evalCount = $stmt->fetchColumn();

            return $assignedCount > 0 && $assignedCount == $evalCount;

        } catch (Exception $e) {
            error_log("EmailQueueHelper::areEvaluationsComplete Error: " . $e->getMessage());
            return false;
        }
    }
}
