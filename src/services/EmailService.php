<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../libs/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../libs/phpmailer/src/Exception.php';

class EmailService
{
    private $db;
    private $mailer;
    private array $config;

    public function __construct($db = null)
    {
        $this->db = $db ?? (new Database())->getConnection();
        $this->config = $this->loadConfig();
    }

    /**
     * Load email configuration from settings
     */
    private function loadConfig(): array
    {
        return [
            'enabled' => Settings::get('email_enabled', '0') === '1',
            'host' => Settings::get('email_host', 'smtp.gmail.com'),
            'port' => (int) Settings::get('email_port', '587'),
            'username' => Settings::get('email_username', ''),
            'password' => Settings::get('email_password', ''),
            'from_name' => Settings::get('email_from_name', AppSettings::getName()),
            'encryption' => Settings::get('email_encryption', 'tls'),
            'batch_size' => (int) Settings::get('email_batch_size', '10'),
        ];
    }

    /**
     * Check if email feature is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] && 
               !empty($this->config['username']) && 
               !empty($this->config['password']);
    }

    /**
     * Configure PHPMailer instance
     */
    private function configureMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host = $this->config['host'];
        $mailer->Port = $this->config['port'];
        $mailer->SMTPSecure = $this->config['encryption'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $this->config['username'];
        $mailer->Password = $this->config['password'];
        $mailer->setFrom($this->config['username'], $this->config['from_name']);
        
        // Set charset
        $mailer->CharSet = 'UTF-8';
        
        // Set timeout
        $mailer->Timeout = 30;

        return $mailer;
    }

    /**
     * Send email directly (synchronous)
     * 
     * @param string $to Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $attachmentPath Path to PDF attachment
     * @return array ['success' => bool, 'message' => string, 'error' => string|null]
     */
    public function sendEmail(string $to, string $toName, string $subject, string $body, ?string $attachmentPath = null): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Email feature is disabled',
                'error' => 'Email feature is not enabled in settings'
            ];
        }

        try {
            $mailer = $this->configureMailer();
            
            $mailer->addAddress($to, $toName);
            $mailer->Subject = $subject;
            $mailer->isHTML(true);
            $mailer->Body = $body;
            
            // Add plain text alternative
            $mailer->AltBody = strip_tags($body);

            // Add attachment if provided
            if ($attachmentPath && file_exists($attachmentPath)) {
                $mailer->addAttachment($attachmentPath);
            }

            $mailer->send();

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'error' => null
            ];

        } catch (Exception $e) {
            error_log("EmailService::sendEmail Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Queue email for asynchronous sending
     * 
     * @param string $to Recipient email
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $attachmentPath Path to PDF attachment
     * @param string $eventType Event type (SEMPRO, SEMHAS, PRA_UJIAN, UJIAN_SKRIPSI)
     * @param int $studentId Student ID
     * @param int $priority Priority (0=normal, 1=high, 2=urgent)
     * @return int|false Queue ID or false on failure
     */
    public function queueEmail(string $to, string $toName, string $subject, string $body, ?string $attachmentPath, string $eventType, int $studentId, int $priority = 0): int|false
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $query = "INSERT INTO email_queue
                (to_email, to_name, subject, body, attachment_path, event_type, student_id, priority, status, scheduled_at)
                VALUES (:to_email, :to_name, :subject, :body, :attachment_path, :event_type, :student_id, :priority, 'PENDING', NOW())";

            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':to_email', $to);
            $stmt->bindParam(':to_name', $toName);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':body', $body);
            $stmt->bindParam(':attachment_path', $attachmentPath);
            $stmt->bindParam(':event_type', $eventType);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':priority', $priority, PDO::PARAM_INT);

            $executeResult = $stmt->execute();

            if ($executeResult) {
                $lastId = $this->db->lastInsertId();
                return (int) $lastId;
            }

            return false;

        } catch (Exception $e) {
            $errorMsg = "EmailService::queueEmail Error: " . $e->getMessage();
            error_log($errorMsg);
            return false;
        } catch (Throwable $e) {
            $errorMsg = "EmailService::queueEmail Throwable: " . $e->getMessage();
            error_log($errorMsg);
            return false;
        }
    }

    /**
     * Process email queue (for cron job)
     * 
     * @param int $limit Number of emails to process
     * @return array ['processed' => int, 'sent' => int, 'failed' => int, 'errors' => array]
     */
    public function processQueue(int $limit = 10): array
    {
        if (!$this->isEnabled()) {
            return [
                'processed' => 0,
                'sent' => 0,
                'failed' => 0,
                'errors' => ['Email feature is disabled']
            ];
        }

        $result = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // Get pending emails
            $query = "SELECT * FROM email_queue 
                WHERE status = 'PENDING' 
                AND scheduled_at <= NOW()
                ORDER BY priority DESC, created_at ASC 
                LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($emails as $email) {
                $result['processed']++;
                
                // Mark as processing
                $this->updateQueueStatus($email['id'], 'PROCESSING');
                
                // Send email
                $sendResult = $this->sendEmail(
                    $email['to_email'],
                    $email['to_name'],
                    $email['subject'],
                    $email['body'],
                    $email['attachment_path']
                );

                if ($sendResult['success']) {
                    $this->updateQueueStatus($email['id'], 'SENT');
                    $this->logEmail($email);
                    $result['sent']++;
                } else {
                    $attempts = $email['attempts'] + 1;
                    $maxAttempts = $email['max_attempts'] ?? 3;
                    
                    if ($attempts >= $maxAttempts) {
                        $this->updateQueueStatus($email['id'], 'FAILED', $sendResult['error'], $attempts);
                        $result['errors'][] = "ID {$email['id']}: " . $sendResult['error'];
                    } else {
                        $this->updateQueueStatus($email['id'], 'PENDING', $sendResult['error'], $attempts);
                        $result['errors'][] = "ID {$email['id']}: " . $sendResult['error'] . " (Retry $attempts/$maxAttempts)";
                    }
                    $result['failed']++;
                }

                // Delay between emails to avoid rate limiting
                if ($result['processed'] < count($emails)) {
                    usleep(500000); // 0.5 second delay
                }
            }

        } catch (Exception $e) {
            $result['errors'][] = "Queue processing error: " . $e->getMessage();
            error_log("EmailService::processQueue Error: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Update queue status
     */
    private function updateQueueStatus(int $id, string $status, ?string $errorMessage = null, ?int $attempts = null): void
    {
        try {
            $query = "UPDATE email_queue SET status = :status";
            $params = [':status' => $status, ':id' => $id];
            
            if ($errorMessage !== null) {
                $query .= ", error_message = :error";
                $params[':error'] = $errorMessage;
            }
            
            if ($attempts !== null) {
                $query .= ", attempts = :attempts";
                $params[':attempts'] = $attempts;
            }
            
            if ($status === 'SENT') {
                $query .= ", sent_at = NOW()";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

        } catch (Exception $e) {
            error_log("EmailService::updateQueueStatus Error: " . $e->getMessage());
        }
    }

    /**
     * Log sent email to email_logs table
     */
    private function logEmail(array $queueData): void
    {
        try {
            $query = "INSERT INTO email_logs
                (recipient_email, recipient_name, subject, event_type, student_id, student_name, pdf_path, status, sent_at)
                VALUES (:recipient_email, :recipient_name, :subject, :event_type, :student_id, :student_name, :pdf_path, 'SENT', NOW())";
            
            // Get student name if student_id is provided
            $studentName = $queueData['student_name'] ?? '';
            if (!empty($queueData['student_id'])) {
                $studentQuery = "SELECT name FROM students WHERE id = :student_id";
                $stmt = $this->db->prepare($studentQuery);
                $stmt->bindParam(':student_id', $queueData['student_id']);
                $stmt->execute();
                $student = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($student) {
                    $studentName = $student['name'];
                }
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':recipient_email', $queueData['to_email']);
            $stmt->bindParam(':recipient_name', $queueData['to_name']);
            $stmt->bindParam(':subject', $queueData['subject']);
            $stmt->bindParam(':event_type', $queueData['event_type']);
            $stmt->bindParam(':student_id', $queueData['student_id']);
            $stmt->bindParam(':student_name', $studentName);
            $stmt->bindParam(':pdf_path', $queueData['attachment_path']);
            $stmt->execute();

        } catch (Exception $e) {
            error_log("EmailService::logEmail Error: " . $e->getMessage());
        }
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        try {
            $query = "SELECT status, COUNT(*) as count FROM email_queue GROUP BY status";
            $stmt = $this->db->query($query);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = [
                'pending' => 0,
                'processing' => 0,
                'sent' => 0,
                'failed' => 0
            ];
            
            foreach ($stats as $row) {
                $result[$row['status']] = (int) $row['count'];
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("EmailService::getQueueStats Error: " . $e->getMessage());
            return ['pending' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0];
        }
    }

    /**
     * Get recent email logs
     */
    public function getRecentLogs(int $limit = 50): array
    {
        try {
            $query = "SELECT * FROM email_logs ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("EmailService::getRecentLogs Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate email body for evaluation notification
     */
    public function generateEvaluationEmailBody(string $eventType, string $studentName, string $studentNim, string $title, string $eventDate): string
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
                    <h2>📋 Notifikasi Penilaian {$eventLabel}</h2>
                </div>
                <div class='content'>
                    <p>Yth. Bapak/Ibu Dosen,</p>
                    <p>Berikut adalah informasi mengenai penilaian {$eventLabel} yang telah selesai:</p>
                    
                    <div class='info-box'>
                        <strong>Nama Mahasiswa:</strong> {$studentName}<br>
                        <strong>NIM:</strong> {$studentNim}<br>
                        <strong>Judul Skripsi:</strong> {$title}<br>
                        <strong>Tanggal Pelaksanaan:</strong> {$eventDate}
                    </div>
                    
                    <div class='pdf-notice'>
                        <strong>📎 Lampiran PDF</strong><br>
                        Dokumen PDF berisi formulir penilaian terlampir pada email ini.
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
     * Generate email subject for evaluation notification
     */
    public function generateEvaluationSubject(string $eventType, string $studentName): string
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
     * Test email configuration
     */
    public function testConfig(): array
    {
        $errors = [];
        
        if (empty($this->config['username'])) {
            $errors[] = 'Email username is not configured';
        }
        
        if (empty($this->config['password'])) {
            $errors[] = 'Email password is not configured';
        }
        
        if (!in_array($this->config['port'], [587, 465, 25])) {
            $errors[] = 'Invalid SMTP port';
        }
        
        if (!in_array($this->config['encryption'], ['tls', 'ssl'])) {
            $errors[] = 'Invalid encryption method';
        }
        
        if (empty($errors)) {
            try {
                $mailer = $this->configureMailer();
                // Try to connect to SMTP
                if ($mailer->getSMTPInstance()->connect()) {
                    $mailer->getSMTPInstance()->close();
                    return ['valid' => true, 'message' => 'Configuration is valid'];
                }
            } catch (Exception $e) {
                $errors[] = 'SMTP connection failed: ' . $e->getMessage();
            }
        }
        
        return ['valid' => false, 'errors' => $errors];
    }
    
    /**
     * Send a test email to verify email configuration
     *
     * @param string $toEmail Recipient email address
     * @return array Result with success status and message
     */
    public function sendTestEmail(string $toEmail): array
    {
        // Check configuration
        if (empty($this->config['username'])) {
            return ['success' => false, 'message' => 'Email pengirim belum diatur. Silakan atur "Email Pengirim" di pengaturan.'];
        }
        
        if (empty($this->config['password'])) {
            return ['success' => false, 'message' => 'Password/App Password belum diatur. Silakan atur di pengaturan.'];
        }
        
        if (!$this->config['enabled']) {
            return ['success' => false, 'message' => 'Fitur email belum diaktifkan. Silakan aktifkan di pengaturan.'];
        }
        
        if (empty($toEmail)) {
            return ['success' => false, 'message' => 'Email Anda belum diatur. Silakan atur email di halaman Profile terlebih dahulu.'];
        }
        
        try {
            $mailer = $this->configureMailer();
            
            // Enable verbose debug output
            $mailer->SMTPDebug = 2; // Shows client-server communication
            $mailer->Debugoutput = function($str, $level) {
                // Log to file for debugging
                error_log("SMTP Debug [$level]: $str");
            };
            
            // Set recipient
            $mailer->addAddress($toEmail);
            
            // Set email content
            $mailer->Subject = 'Email Test - ' . AppSettings::getName();
            $mailer->Body = $this->getTestEmailBody($toEmail);
            $mailer->isHTML(true);
            
            // Generate test PDF attachment
            $pdfContent = $this->generateTestPdf();
            $mailer->addStringAttachment($pdfContent, 'test_email_kombi.pdf', 'base64', 'application/pdf');
            
            // Send email
            if (!$mailer->send()) {
                $errorMsg = 'Gagal mengirim email: ' . $mailer->ErrorInfo;
                
                // Add more specific error info
                if (strpos($mailer->ErrorInfo, 'authentication') !== false) {
                    $errorMsg .= ' (Error: Autentikasi gagal. Pastikan App Password Gmail sudah benar)';
                } elseif (strpos($mailer->ErrorInfo, 'connection') !== false) {
                    $errorMsg .= ' (Error: Koneksi ke SMTP gagal. Cek host dan port)';
                } elseif (strpos($mailer->ErrorInfo, 'TLS') !== false) {
                    $errorMsg .= ' (Error: Masalah enkripsi TLS/SSL. Cek pengaturan enkripsi)';
                }
                
                return ['success' => false, 'message' => $errorMsg];
            }
            
            // Log success
            $logData = [
                'to_email' => $toEmail,
                'to_name' => 'Test User',
                'subject' => 'Email Test - ' . AppSettings::getName(),
                'event_type' => 'TEST',
                'student_id' => null,
                'student_name' => 'N/A',
                'pdf_path' => null
            ];
            $this->logEmail($logData);
            
            return ['success' => true, 'message' => 'Email test berhasil dikirim ke ' . $toEmail];
            
        } catch (Exception $e) {
            // Log error
            $logData = [
                'to_email' => $toEmail,
                'to_name' => 'Test User',
                'subject' => 'Email Test - ' . AppSettings::getName(),
                'event_type' => 'TEST',
                'student_id' => null,
                'student_name' => 'N/A',
                'pdf_path' => null
            ];
            $this->logEmail($logData);
            
            $errorMsg = 'Gagal mengirim email: ' . $e->getMessage();
            
            // Add more specific error info for common issues
            if (strpos($e->getMessage(), 'authentication') !== false) {
                $errorMsg .= ' (Tips: Cek kembali App Password Gmail Anda)';
            } elseif (strpos($e->getMessage(), 'connection') !== false) {
                $errorMsg .= ' (Tips: Pastikan koneksi internet stabil dan firewall tidak memblokir port SMTP)';
            }
            
            return ['success' => false, 'message' => $errorMsg];
        }
    }
    
    /**
     * Generate a test PDF for email attachment
     */
    private function generateTestPdf(): string
    {
        require_once __DIR__ . '/../libs/fpdf/fpdf.php';
        
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Email Test - ' . AppSettings::getName(), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Content
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Ini adalah file PDF test untuk memverifikasi', 0, 1, 'C');
        $pdf->Cell(0, 10, 'bahwa konfigurasi email sudah benar.', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Details
        $pdf->Cell(0, 8, 'Detail Pengiriman:', 0, 1, 'L');
        $pdf->Cell(0, 8, 'Waktu: ' . date('d-m-Y H:i:s'), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Penerima: ' . (func_num_args() > 0 ? func_get_arg(0) : 'User'), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Status: Berhasil', 0, 1, 'L');
        $pdf->Ln(10);
        
        // Info
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->MultiCell(0, 6, 'File ini menunjukkan bahwa sistem dapat membuat dan mengirim PDF melalui email. Konfigurasi email Anda sudah benar dan siap digunakan.');
        
        return $pdf->Output('S');
    }
    
    /**
     * Get test email body
     */
    private function getTestEmailBody(string $toEmail): string
    {
        $timestamp = date('d-m-Y H:i:s');
        
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
                <h2 style="color: white; margin: 0;">✅ Email Test Berhasil!</h2>
            </div>
            <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #dee2e6;">
                <p style="font-size: 16px; color: #333;">Ini adalah email test dari <strong><?= htmlspecialchars(AppSettings::getName()) ?></strong>.</p>
                
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
                    <h3 style="color: #28a745; margin-top: 0;">Konfigurasi Email Berhasil</h3>
                    <p style="color: #666; margin-bottom: 0;">Konfigurasi email SMTP Anda sudah benar dan siap digunakan untuk mengirim notifikasi ke dosen.</p>
                </div>
                
                <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <h4 style="color: #0056b3; margin-top: 0;">Detail Pengiriman:</h4>
                    <ul style="color: #666; margin-bottom: 0;">
                        <li><strong>Waktu:</strong> ' . $timestamp . '</li>
                        <li><strong>Penerima:</strong> ' . htmlspecialchars($toEmail) . '</li>
                        <li><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">Berhasil</span></li>
                    </ul>
                </div>
                
                <p style="color: #999; font-size: 14px; margin: 30px 0 0 0; text-align: center;">
                    Email ini dikirim secara otomatis oleh <?= htmlspecialchars(AppSettings::getName()) ?>.<br>
                    Jangan balas email ini.
                </p>
            </div>
        </div>';
    }

    /**
     * Diagnose email configuration and connectivity
     * Returns detailed diagnostic information for troubleshooting
     *
     * @return array Diagnostic results with checks and recommendations
     */
    public function diagnoseEmailConfiguration(): array
    {
        $results = [
            'status' => 'unknown',
            'checks' => [],
            'recommendations' => [],
            'config' => [
                'enabled' => $this->config['enabled'],
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'encryption' => $this->config['encryption'],
                'username_set' => !empty($this->config['username']),
                'password_set' => !empty($this->config['password']),
            ]
        ];

        // 1. Check PHP Extensions
        $results['checks']['php_extensions'] = $this->checkPhpExtensions();
        
        // 2. Check DNS Resolution
        $results['checks']['dns_resolution'] = $this->checkDnsResolution();
        
        // 3. Check Port Connectivity
        $results['checks']['port_connectivity'] = $this->checkPortConnectivity();
        
        // 4. Check SMTP Connection
        $results['checks']['smtp_connection'] = $this->checkSmtpConnection();
        
        // 5. Check Configuration
        $results['checks']['configuration'] = $this->checkConfiguration();

        // Generate recommendations based on checks
        $results['recommendations'] = $this->generateRecommendations($results['checks']);

        // Determine overall status
        $allPassed = true;
        foreach ($results['checks'] as $check) {
            if ($check['status'] !== 'pass') {
                $allPassed = false;
                break;
            }
        }
        $results['status'] = $allPassed ? 'pass' : 'fail';

        return $results;
    }

    /**
     * Check required PHP extensions
     */
    private function checkPhpExtensions(): array
    {
        $result = [
            'name' => 'PHP Extensions',
            'status' => 'pass',
            'details' => [],
            'error' => null
        ];

        $requiredExtensions = ['openssl', 'sockets', 'mbstring'];
        $missing = [];

        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $result['details'][$ext] = $loaded ? 'Installed' : 'Missing';
            if (!$loaded) {
                $missing[] = $ext;
            }
        }

        if (!empty($missing)) {
            $result['status'] = 'fail';
            $result['error'] = 'Missing PHP extensions: ' . implode(', ', $missing);
        }

        return $result;
    }

    /**
     * Check DNS resolution for SMTP host
     */
    private function checkDnsResolution(): array
    {
        $result = [
            'name' => 'DNS Resolution',
            'status' => 'pass',
            'details' => [],
            'error' => null
        ];

        $host = $this->config['host'];
        
        // Check if host is empty
        if (empty($host)) {
            $result['status'] = 'fail';
            $result['error'] = 'SMTP host is not configured';
            return $result;
        }

        // Try to resolve hostname
        $ip = gethostbyname($host);
        
        if ($ip === $host) {
            // DNS resolution failed
            $result['status'] = 'fail';
            $result['error'] = "Cannot resolve hostname: $host";
            $result['details']['resolution'] = 'Failed';
        } else {
            $result['details']['hostname'] = $host;
            $result['details']['ip_address'] = $ip;
            $result['details']['resolution'] = 'Success';
        }

        return $result;
    }

    /**
     * Check if SMTP port is reachable
     */
    private function checkPortConnectivity(): array
    {
        $result = [
            'name' => 'Port Connectivity',
            'status' => 'pass',
            'details' => [],
            'error' => null
        ];

        $host = $this->config['host'];
        $port = $this->config['port'];
        $timeout = 5;

        if (empty($host) || empty($port)) {
            $result['status'] = 'fail';
            $result['error'] = 'SMTP host or port is not configured';
            return $result;
        }

        // Try to connect to the port
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($socket) {
            fclose($socket);
            $result['details'][$host . ':' . $port] = 'Open';
            $result['details']['connection'] = 'Success';
        } else {
            $result['status'] = 'fail';
            $result['error'] = "Cannot connect to $host:$port - Error: $errstr ($errno)";
            $result['details'][$host . ':' . $port] = 'Blocked/Filtered';
            $result['details']['connection'] = 'Failed';
        }

        return $result;
    }

    /**
     * Check SMTP connection with authentication
     */
    private function checkSmtpConnection(): array
    {
        $result = [
            'name' => 'SMTP Connection',
            'status' => 'pass',
            'details' => [],
            'error' => null
        ];

        if (!$this->config['enabled']) {
            $result['status'] = 'skip';
            $result['error'] = 'Email feature is disabled';
            return $result;
        }

        if (empty($this->config['username']) || empty($this->config['password'])) {
            $result['status'] = 'skip';
            $result['error'] = 'Email credentials not configured';
            return $result;
        }

        try {
            $mailer = new PHPMailer(true);
            
            // Enable verbose debug
            $debugLog = [];
            $mailer->SMTPDebug = 3;
            $mailer->Debugoutput = function($str, $level) use (&$debugLog) {
                $debugLog[] = trim($str);
            };

            $mailer->isSMTP();
            $mailer->Host = $this->config['host'];
            $mailer->Port = $this->config['port'];
            $mailer->SMTPSecure = $this->config['encryption'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->config['username'];
            $mailer->Password = $this->config['password'];
            $mailer->Timeout = 10;

            // Try to connect by calling smtpConnect()
            $mailer->smtpConnect();
            
            // If we get here, connection succeeded
            $result['details']['connection'] = 'Success';
            $result['details']['authentication'] = 'Success';

        } catch (Exception $e) {
            $result['status'] = 'fail';
            $errorMsg = $e->getMessage();
            $result['error'] = $errorMsg;
            $result['details']['connection'] = 'Failed';
            
            // Parse error for specific issues
            if (strpos($errorMsg, 'Could not connect') !== false) {
                $result['details']['issue'] = 'Connection refused - Check firewall/port';
            } elseif (strpos($errorMsg, 'authentication') !== false || strpos($errorMsg, 'AUTH') !== false) {
                $result['details']['issue'] = 'Authentication failed - Check username/password';
            } elseif (strpos($errorMsg, 'TLS') !== false || strpos($errorMsg, 'SSL') !== false) {
                $result['details']['issue'] = 'Encryption error - Check encryption settings';
            }
            
            $result['details']['debug_log'] = array_slice($debugLog, -10); // Last 10 lines
        }

        return $result;
    }

    /**
     * Check email configuration settings
     */
    private function checkConfiguration(): array
    {
        $result = [
            'name' => 'Configuration',
            'status' => 'pass',
            'details' => [],
            'error' => null
        ];

        $issues = [];

        if (!$this->config['enabled']) {
            $issues[] = 'Email feature is disabled';
        }

        if (empty($this->config['host'])) {
            $issues[] = 'SMTP host is not set';
        }

        if (empty($this->config['port'])) {
            $issues[] = 'SMTP port is not set';
        }

        if (empty($this->config['username'])) {
            $issues[] = 'Email username is not set';
        }

        if (empty($this->config['password'])) {
            $issues[] = 'Email password is not set';
        }

        // Validate port number
        if (!empty($this->config['port'])) {
            $validPorts = [25, 465, 587, 2525];
            if (!in_array($this->config['port'], $validPorts)) {
                $issues[] = "Port {$this->config['port']} is not a common SMTP port";
            }
        }

        // Validate encryption
        if (!empty($this->config['encryption'])) {
            $validEncryptions = ['tls', 'ssl', 'none'];
            if (!in_array(strtolower($this->config['encryption']), $validEncryptions)) {
                $issues[] = "Invalid encryption: {$this->config['encryption']}";
            }
        }

        // Check port/encryption combination
        if ($this->config['port'] == 587 && strtolower($this->config['encryption']) != 'tls') {
            $issues[] = "Port 587 should use TLS encryption";
        }
        if ($this->config['port'] == 465 && strtolower($this->config['encryption']) != 'ssl') {
            $issues[] = "Port 465 should use SSL encryption";
        }

        if (!empty($issues)) {
            $result['status'] = 'fail';
            $result['error'] = implode('; ', $issues);
        }

        $result['details']['issues_found'] = count($issues);
        $result['details']['issues'] = $issues;

        return $result;
    }

    /**
     * Generate recommendations based on check results
     */
    private function generateRecommendations(array $checks): array
    {
        $recommendations = [];

        // PHP Extensions recommendations
        if ($checks['php_extensions']['status'] !== 'pass') {
            $recommendations[] = [
                'priority' => 'critical',
                'title' => 'Install Required PHP Extensions',
                'description' => 'Install missing PHP extensions: ' . implode(', ', array_keys(array_filter($checks['php_extensions']['details'], fn($v) => $v === 'Missing'))),
                'commands' => [
                    'Ubuntu/Debian: sudo apt install php-openssl php-sockets php-mbstring',
                    'CentOS/RHEL: sudo yum install php-openssl php-sockets php-mbstring',
                ]
            ];
        }

        // DNS recommendations
        if ($checks['dns_resolution']['status'] !== 'pass') {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Fix DNS Resolution',
                'description' => 'Server cannot resolve the SMTP hostname. Check /etc/resolv.conf',
                'commands' => [
                    'Check DNS: cat /etc/resolv.conf',
                    'Test DNS: nslookup ' . $this->config['host'],
                    'Try using Google DNS: echo "nameserver 8.8.8.8" | sudo tee -a /etc/resolv.conf',
                ]
            ];
        }

        // Port connectivity recommendations
        if ($checks['port_connectivity']['status'] !== 'pass') {
            $recommendations[] = [
                'priority' => 'critical',
                'title' => 'Open Firewall Port',
                'description' => "Port {$this->config['port']} is blocked. Open it in the firewall.",
                'commands' => [
                    'Ubuntu/Debian: sudo ufw allow ' . $this->config['port'] . '/tcp',
                    'CentOS/RHEL: sudo firewall-cmd --permanent --add-port=' . $this->config['port'] . '/tcp && sudo firewall-cmd --reload',
                ]
            ];

            // Add SELinux recommendation for CentOS/RHEL
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Check SELinux (CentOS/RHEL)',
                'description' => 'SELinux might be blocking outbound connections',
                'commands' => [
                    'Check status: sestatus',
                    'Allow HTTP network: sudo setsebool -P httpd_can_network_connect on',
                ]
            ];
        }

        // SMTP connection recommendations
        if (isset($checks['smtp_connection']) && $checks['smtp_connection']['status'] !== 'pass' && $checks['smtp_connection']['status'] !== 'skip') {
            if (isset($checks['smtp_connection']['details']['issue'])) {
                $issue = $checks['smtp_connection']['details']['issue'];
                
                if (strpos($issue, 'authentication') !== false) {
                    $recommendations[] = [
                        'priority' => 'high',
                        'title' => 'Fix Authentication',
                        'description' => 'Check your email credentials. For Gmail, use an App Password instead of your regular password.',
                        'commands' => [
                            'Create App Password: https://myaccount.google.com/apppasswords',
                            'Enable 2-Step Verification first if not enabled',
                        ]
                    ];
                } elseif (strpos($issue, 'TLS') !== false || strpos($issue, 'SSL') !== false) {
                    $recommendations[] = [
                        'priority' => 'medium',
                        'title' => 'Fix Encryption Settings',
                        'description' => 'Check encryption settings. Port 587 uses TLS, port 465 uses SSL',
                        'commands' => [
                            'Port 587: Set encryption to "tls"',
                            'Port 465: Set encryption to "ssl"',
                        ]
                    ];
                }
            }
        }

        // Configuration recommendations
        if ($checks['configuration']['status'] !== 'pass') {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Complete Email Configuration',
                'description' => 'Fill in all required email settings in the Settings page',
                'commands' => [
                    'Go to Settings page',
                    'Enable email feature',
                    'Set SMTP Host, Port, Username, Password',
                ]
            ];
        }

        return $recommendations;
    }

    /**
     * Queue email notification to student when their score is edited
     *
     * @param int $studentId Student ID
     * @param string $stage Evaluation stage (sempro, semhas, pra-ujian, ujian)
     * @param float $oldScore Old score value
     * @param float $newScore New score value
     * @param string $editReason Reason for the edit
     * @param string $editorName Name of the person who edited the score
     * @return bool True if email was queued successfully
     */
    public function queueScoreEditNotification(int $studentId, string $stage, float $oldScore, float $newScore, string $editReason, string $editorName): bool
    {
        // Check if notification is enabled
        if (!Settings::shouldNotifyStudentOnScoreEdit()) {
            return false;
        }

        if (!$this->isEnabled()) {
            return false;
        }

        try {
            // Get student data
            $query = "SELECT s.name, s.email, s.nim FROM students s WHERE s.id = :student_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student || empty($student['email'])) {
                return false;
            }

            // Stage labels
            $stageLabels = [
                'sempro' => 'Seminar Proposal',
                'semhas' => 'Seminar Hasil',
                'pra-ujian' => 'Pra-Ujian Skripsi',
                'ujian' => 'Ujian Skripsi'
            ];
            $stageLabel = $stageLabels[$stage] ?? $stage;

            // Create email body
            $subject = "Notifikasi Perubahan Nilai - {$stageLabel}";
            
            $body = $this->renderScoreEditEmailTemplate([
                'studentName' => $student['name'],
                'nim' => $student['nim'],
                'stageLabel' => $stageLabel,
                'oldScore' => number_format($oldScore, 2),
                'newScore' => number_format($newScore, 2),
                'editReason' => htmlspecialchars($editReason),
                'editorName' => htmlspecialchars($editorName),
                'editDate' => date('d M Y H:i'),
                'appName' => AppSettings::getName()
            ]);

            // Queue email
            $queueId = $this->queueEmail(
                $student['email'],
                $student['name'],
                $subject,
                $body,
                null, // No attachment
                'SCORE_EDIT', // Event type
                $studentId,
                1 // High priority
            );

            return $queueId !== false;

        } catch (Exception $e) {
            error_log("EmailService::queueScoreEditNotification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Render email template for score edit notification
     */
    private function renderScoreEditEmailTemplate(array $data): string
    {
        $appName = $data['appName'] ?? 'KomBi-TIP';
        
        $html = "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Notifikasi Perubahan Nilai</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0066cc; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none; }
        .score-change { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        .footer { background-color: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
        .label { font-weight: bold; }
        .old-score { color: #dc3545; font-weight: bold; }
        .new-score { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🔔 Notifikasi Perubahan Nilai</h2>
        </div>
        <div class='content'>
            <p>Yth. <strong>{$data['studentName']}</strong> (NIM: {$data['nim']}),</p>
            
            <p>Kami informasikan bahwa nilai Anda untuk tahap <strong>{$data['stageLabel']}</strong> telah diperbarui oleh dosen pembimbing/penguji.</p>
            
            <div class='score-change'>
                <p><span class='label'>Nilai Sebelumnya:</span> <span class='old-score'>{$data['oldScore']}</span></p>
                <p><span class='label'>Nilai Baru:</span> <span class='new-score'>{$data['newScore']}</span></p>
                <p><span class='label'>Alasan Perubahan:</span> {$data['editReason']}</p>
            </div>
            
            <p><span class='label'>Diedit oleh:</span> {$data['editorName']}</p>
            <p><span class='label'>Waktu:</span> {$data['editDate']}</p>
            
            <p>Jika Anda memiliki pertanyaan mengenai perubahan nilai ini, silakan hubungi dosen pembimbing atau Kombi.</p>
            
            <p>Terima kasih.</p>
        </div>
        <div class='footer'>
            <p>Email ini dikirim secara otomatis oleh <strong>{$appName}</strong>.</p>
            <p>Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>";

        return $html;
    }
}
