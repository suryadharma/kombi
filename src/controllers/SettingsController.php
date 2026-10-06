<?php

require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../helpers/BackupSettingsHelper.php';

class SettingsController extends BaseController
{
    public function index() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can access settings
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get list of all angkatan
            $angkatanQuery = "SELECT DISTINCT angkatan FROM students WHERE status != 'LULUS' ORDER BY angkatan DESC";
            $angkatanStmt = $db->prepare($angkatanQuery);
            $angkatanStmt->execute();
            $allAngkatan = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get active angkatan from settings
            $settingQuery = "SELECT value FROM settings WHERE key_name = 'active_angkatan' LIMIT 1";
            $settingStmt = $db->prepare($settingQuery);
            $settingStmt->execute();
            $setting = $settingStmt->fetch(PDO::FETCH_ASSOC);
            
            $activeAngkatan = [];
            if ($setting) {
                $activeAngkatan = explode(',', $setting['value']);
                $activeAngkatan = array_map('trim', $activeAngkatan);
            }
            
            $slaThresholds = Settings::getSlaThresholds();
            $stageProgressSla = Settings::getStageProgressSlaConfig();

            // Get general app settings
            $appSettings = [
                'app_name' => Settings::get('app_name', 'KomBi-TIP Apps'),
                'app_full_name' => Settings::get('app_full_name', 'Sistem Komisi Bimbingan Skripsi'),
                'app_short_name' => Settings::get('app_short_name', 'KomBi-TIP'),
                'org_prodi' => Settings::get('org_prodi', 'Program Studi Teknologi Industri Pertanian'),
                'org_fakultas' => Settings::get('org_fakultas', 'Fakultas Teknologi Pertanian'),
                'org_universitas' => Settings::get('org_universitas', 'Universitas Jember'),
                'org_location' => Settings::get('org_location', 'Jember'),
                'app_footer_text' => Settings::get('app_footer_text', 'KomBi-TIP Apps'),
                'app_developer_name' => Settings::get('app_developer_name', 'BS'),
                'app_developer_url' => Settings::get('app_developer_url', 'https://www.suryadharma.work/'),
                'app_admin_contact' => Settings::get('app_admin_contact', 'admin@kombi.unej.ac.id'),
            ];

            // Get email settings (simplified)
            $emailSettings = [
                'enabled' => Settings::get('email_enabled', '0'),
                'email_mode' => Settings::get('email_mode', 'hybrid'), // instant, batch, or hybrid
                'email_gmail' => Settings::get('email_username', ''),
                'app_password' => Settings::get('email_password', ''),
                'port' => Settings::get('email_port', '587'),
                'encryption' => Settings::get('email_encryption', 'tls'),
            ];

            // Get email queue stats
            $emailQueueStats = null;
            try {
                $emailService = new EmailService($db);
                $emailQueueStats = $emailService->getQueueStats();
            } catch (Exception $e) {
                $emailQueueStats = null;
            }

            // Get evaluation settings
            $evaluationSettings = [
                'edit_window_hours' => Settings::getEvaluationEditWindowHours(),
                'notify_on_edit' => Settings::shouldNotifyStudentOnScoreEdit(),
            ];
            
            // Get backup settings for superadmin
            $backupSettings = [];
            if ($role === 'superadmin') {
                try {
                    $backupSettings = BackupSettingsHelper::getSettings($db);
                } catch (Exception $e) {
                    $backupSettings = [];
                }
            }
            
            $this->render('settings/index', [
                'allAngkatan' => $allAngkatan,
                'activeAngkatan' => $activeAngkatan,
                'slaThresholds' => $slaThresholds,
                'stageProgressSla' => $stageProgressSla,
                'scoreVisibility' => Settings::getScoreVisibilityConfig(),
                'appSettings' => $appSettings,
                'emailSettings' => $emailSettings,
                'emailQueueStats' => $emailQueueStats,
                'evaluationSettings' => $evaluationSettings,
                'backupSettings' => $backupSettings,
                'userRole' => $role
            ]);
        } catch (Exception $e) {
            $this->render('settings/index', [
                'error' => 'Gagal memuat pengaturan: ' . $e->getMessage(),
                'scoreVisibility' => Settings::getScoreVisibilityConfig()
            ]);
        }
    }
    
    public function update() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can access settings
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($csrfToken)) {
            $this->render('settings/index', [
                'error' => 'Token keamanan tidak valid. Silakan coba lagi.'
            ]);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Get section parameter to determine which settings to save
        $section = $_POST['section'] ?? 'all';
        if (empty($section)) {
            $section = 'all';
        }

        try {
            $upsertQuery = "INSERT INTO settings (key_name, value) VALUES (:key, :value)
                            ON DUPLICATE KEY UPDATE value = :value";
            $upsertStmt = $db->prepare($upsertQuery);
            
            // Save based on section
            if ($section === 'all' || $section === 'active_angkatan') {
                $activeAngkatan = $_POST['active_angkatan'] ?? [];
                $activeAngkatanStr = implode(',', $activeAngkatan);
                $upsertStmt->execute([
                    ':key' => 'active_angkatan',
                    ':value' => $activeAngkatanStr
                ]);
            }
            
            if ($section === 'all' || $section === 'score_visibility') {
                $scoreVisibilityInput = $_POST['score_visibility'] ?? [];
                $stageMap = [
                    'sempro' => 'Seminar Proposal',
                    'semhas' => 'Seminar Hasil',
                    'pra-ujian' => 'Pra-Ujian',
                    'ujian' => 'Ujian Skripsi',
                    'final_score' => 'Nilai Akhir'
                ];
                foreach ($stageMap as $stageKey => $label) {
                    $normalized = str_replace('-', '_', $stageKey);
                    $settingKey = 'score_visibility_' . $normalized;
                    $isEnabled = isset($scoreVisibilityInput[$stageKey]);
                    $upsertStmt->execute([
                        ':key' => $settingKey,
                        ':value' => $isEnabled ? '1' : '0'
                    ]);
                }
            }
            
            if ($section === 'all' || $section === 'sla_threshold') {
                $slaInput = $_POST['sla_threshold'] ?? [];
                $stageMap = [
                    'sempro' => 'Seminar Proposal',
                    'semhas' => 'Seminar Hasil',
                    'pra-ujian' => 'Pra-Ujian',
                    'ujian' => 'Ujian Skripsi'
                ];
                foreach ($stageMap as $stageKey => $label) {
                    $normalized = str_replace('-', '_', $stageKey);
                    $settingKey = 'sla_' . $normalized . '_hours';
                    $value = isset($slaInput[$stageKey]) ? (int)$slaInput[$stageKey] : 72;
                    if ($value < 1) {
                        $value = 1;
                    }
                    $upsertStmt->execute([
                        ':key' => $settingKey,
                        ':value' => $value
                    ]);
                }
            }
            
            if ($section === 'all' || $section === 'progress_sla') {
                $progressInput = $_POST['progress_sla'] ?? [];
                $transitionMap = [
                    'sempro_semhas' => ['from' => 'sempro', 'to' => 'semhas', 'default' => 30],
                    'semhas_pra-ujian' => ['from' => 'semhas', 'to' => 'pra-ujian', 'default' => 30],
                    'pra-ujian_ujian' => ['from' => 'pra-ujian', 'to' => 'ujian', 'default' => 30],
                ];

                foreach ($transitionMap as $key => $config) {
                    $from = str_replace('-', '_', $config['from']);
                    $to = str_replace('-', '_', $config['to']);
                    $settingKey = sprintf('sla_%s_to_%s_days', $from, $to);
                    $value = isset($progressInput[$key]) ? (int) $progressInput[$key] : $config['default'];
                    if ($value < 1) {
                        $value = $config['default'];
                    }

                    $upsertStmt->execute([
                        ':key' => $settingKey,
                        ':value' => $value
                    ]);
                }
            }
            
            // Handle general app settings - only for superadmin (without logo settings)
            // Only save if section explicitly requests it OR if section is 'all' AND app data is actually provided
            if ($role === 'superadmin') {
                $appInput = $_POST['app'] ?? [];
                $shouldSaveAppSettings = ($section === 'app_name' || $section === 'org_info' || $section === 'footer_contact');
                
                // When section is 'all', only save app settings if data is actually provided
                if ($section === 'all' && !empty($appInput)) {
                    $shouldSaveAppSettings = true;
                }
                
                if ($shouldSaveAppSettings) {
                    $appSettingsKeys = [
                        'app_name', 'app_full_name', 'app_short_name',
                        'org_prodi', 'org_fakultas', 'org_universitas', 'org_location',
                        'app_footer_text', 'app_developer_name', 'app_developer_url', 'app_admin_contact'
                    ];
                    
                    // Get existing values to preserve them if not provided
                    $existingAppSettings = [];
                    foreach ($appSettingsKeys as $key) {
                        $existingAppSettings[$key] = Settings::get($key, '');
                    }
                    
                    foreach ($appSettingsKeys as $key) {
                        // Only update if new value is provided and not empty, otherwise keep existing value
                        $newValue = isset($appInput[$key]) && $appInput[$key] !== '' ? $appInput[$key] : $existingAppSettings[$key];
                        $upsertStmt->execute([
                            ':key' => $key,
                            ':value' => $newValue
                        ]);
                    }
                }
            }

            // Handle email settings - only for superadmin (simplified)
            if ($role === 'superadmin' && ($section === 'all' || $section === 'email_config')) {
                $emailInput = $_POST['email'] ?? [];
                
                // Get existing email settings to preserve them if not provided
                $existingEmailSettings = [];
                $emailKeysToCheck = ['email_username', 'email_password', 'email_port', 'email_encryption', 'email_enabled', 'email_mode'];
                foreach ($emailKeysToCheck as $key) {
                    $existingEmailSettings[$key] = Settings::get($key, '');
                }
                
                // Email settings from form - use existing values if new ones are empty
                $emailSettings = [
                    'email_enabled' => isset($emailInput['enabled']) ? '1' : '0',
                    'email_mode' => $emailInput['email_mode'] ?? $existingEmailSettings['email_mode'] ?? 'hybrid',
                    'email_username' => (!empty($emailInput['email_gmail'])) ? $emailInput['email_gmail'] : $existingEmailSettings['email_username'],
                    'email_password' => (!empty($emailInput['app_password'])) ? $emailInput['app_password'] : $existingEmailSettings['email_password'],
                    'email_port' => (!empty($emailInput['port'])) ? $emailInput['port'] : $existingEmailSettings['email_port'],
                    'email_encryption' => (!empty($emailInput['encryption'])) ? $emailInput['encryption'] : $existingEmailSettings['email_encryption'],
                ];
                
                // Hardcoded defaults for other advanced settings
                $defaultEmailSettings = [
                    'email_host' => 'smtp.gmail.com',
                    'email_from_name' => 'Sistem Kombi',
                    'email_batch_size' => '10',
                    'email_retry_delay' => '5',
                    'email_max_queue_age' => '24',
                    'email_delay_minutes' => '5',
                ];
                
                // Save user-provided settings
                foreach ($emailSettings as $key => $value) {
                    $upsertStmt->execute([
                        ':key' => $key,
                        ':value' => $value
                    ]);
                }
                
                // Save default settings only if they don't exist
                foreach ($defaultEmailSettings as $key => $value) {
                    $checkQuery = "SELECT value FROM settings WHERE key_name = :key LIMIT 1";
                    $checkStmt = $db->prepare($checkQuery);
                    $checkStmt->execute([':key' => $key]);
                    if (!$checkStmt->fetch()) {
                        $upsertStmt->execute([
                            ':key' => $key,
                            ':value' => $value
                        ]);
                    }
                }
            }
            
            // Handle workload settings - for kombi and superadmin
            if (($role === 'kombi' || $role === 'superadmin') && ($section === 'all' || $section === 'workload')) {
                $workloadInput = $_POST['workload'] ?? [];
                
                $maxPembimbing = isset($workloadInput['max_pembimbing']) ? (int)$workloadInput['max_pembimbing'] : 8;
                $maxPenguji = isset($workloadInput['max_penguji']) ? (int)$workloadInput['max_penguji'] : 10;
                
                // Validate values
                if ($maxPembimbing < 1) $maxPembimbing = 1;
                if ($maxPembimbing > 50) $maxPembimbing = 50;
                if ($maxPenguji < 1) $maxPenguji = 1;
                if ($maxPenguji > 50) $maxPenguji = 50;
                
                $upsertStmt->execute([
                    ':key' => 'max_pembimbing',
                    ':value' => $maxPembimbing
                ]);
                
                $upsertStmt->execute([
                    ':key' => 'max_penguji',
                    ':value' => $maxPenguji
                ]);
            }
            
            // Handle evaluation settings - for kombi and superadmin
            if (($role === 'kombi' || $role === 'superadmin') && ($section === 'all' || $section === 'evaluation_config')) {
                $evaluationInput = $_POST['evaluation'] ?? [];
                
                // Edit window duration (in hours)
                $editWindowHours = isset($evaluationInput['edit_window_hours']) ? (int)$evaluationInput['edit_window_hours'] : 48;
                if ($editWindowHours < 1) $editWindowHours = 1;
                if ($editWindowHours > 168) $editWindowHours = 168; // Max 7 days
                
                $upsertStmt->execute([
                    ':key' => 'evaluation_edit_window_hours',
                    ':value' => $editWindowHours
                ]);
                
                // Note: Email notification to student on score edit has been disabled
                // Score changes are lecturer privacy and should not be sent to students
            }
            
            // Clear settings cache to ensure new values are used
            Settings::clearCache();
            
            // Check if this is an AJAX request (individual section save)
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Return JSON response for AJAX requests
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Pengaturan berhasil disimpan!']);
                exit;
            }
            
            // Redirect to settings page to see the changes
            $this->redirect('/settings');
        } catch (Exception $e) {
            // Database connection
            $database = new Database();
            $db = $database->getConnection();
            
            // Get list of all angkatan
            $angkatanQuery = "SELECT DISTINCT angkatan FROM students WHERE status != 'LULUS' ORDER BY angkatan DESC";
            $angkatanStmt = $db->prepare($angkatanQuery);
            $angkatanStmt->execute();
            $allAngkatan = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $slaThresholds = Settings::getSlaThresholds();
            $stageProgressSla = Settings::getStageProgressSlaConfig();
            $scoreVisibility = [];
            foreach (['sempro','semhas','pra-ujian','ujian'] as $stageKey) {
                $scoreVisibility[$stageKey] = isset($scoreVisibilityInput[$stageKey]);
            }
            $this->render('settings/index', [
                'error' => 'Gagal menyimpan pengaturan: ' . $e->getMessage(),
                'allAngkatan' => $allAngkatan,
                'activeAngkatan' => $_POST['active_angkatan'] ?? [],
                'slaThresholds' => $slaThresholds,
                'stageProgressSla' => $stageProgressSla,
                'scoreVisibility' => $scoreVisibility
            ]);
        }
    }
    
    /**
     * Send test email to verify email configuration
     */
    public function testEmail()
    {
        // Require authentication
        $this->requireAuth();
        
        // Only superadmin can send test email
        $role = $this->getUserRole();
        if ($role !== 'superadmin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya superadmin yang dapat mengirim email test.']);
            return;
        }
        
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get current user email
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                throw new Exception('User not found');
            }

            $userQuery = "SELECT email FROM users WHERE id = :user_id LIMIT 1";
            $userStmt = $db->prepare($userQuery);
            $userStmt->execute([':user_id' => $userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || empty($user['email'])) {
                throw new Exception('Email pengguna tidak ditemukan. Silakan set email di profil Anda terlebih dahulu.');
            }

            $toEmail = $user['email'];

            // Send test email using EmailService
            $emailService = new EmailService($db);
            $result = $emailService->sendTestEmail($toEmail);

            if ($result['success']) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'to' => $toEmail]);
            } else {
                header('Content-Type: application/json');
                $errorMsg = $result['message'] ?? 'Gagal mengirim email test';
                echo json_encode(['success' => false, 'message' => $errorMsg]);
            }
        } catch (Exception $e) {
            error_log('Test email error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Diagnose email configuration and return detailed results
     */
    public function diagnoseEmail()
    {
        // Require authentication
        $this->requireAuth();
        
        // Only superadmin can diagnose email
        $role = $this->getUserRole();
        if ($role !== 'superadmin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya superadmin yang dapat mendiagnosa email.']);
            return;
        }
        
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($csrfToken)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $emailService = new EmailService($db);
            $result = $emailService->diagnoseEmailConfiguration();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Throwable $e) {
            error_log('Email diagnostic error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }
}
