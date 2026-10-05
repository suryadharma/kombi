<?php

require_once __DIR__ . '/../helpers/BackupSettingsHelper.php';

/**
 * BackupSettingsController
 * 
 * Controller untuk mengelola pengaturan backup (SMB/FTP)
 */
class BackupSettingsController extends BaseController
{
    /**
     * Display backup settings form
     */
    public function index()
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if (!in_array($role, ['superadmin', 'kombi'], true)) {
            $this->setFlash('error', 'Akses ditolak.');
            $this->redirect('/dashboard');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $settings = BackupSettingsHelper::getSettings($db);

        $this->render('settings/backup', [
            'settings' => $settings,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    /**
     * Save backup settings
     */
    public function save()
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if (!in_array($role, ['superadmin', 'kombi'], true)) {
            $this->setFlash('error', 'Akses ditolak.');
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($csrfToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        // Get current settings to preserve passwords if not changed
        $currentSettings = BackupSettingsHelper::getSettings($db);

        $data = [
            'backup_enabled' => !empty($_POST['backup_enabled']),
            'backup_type' => $_POST['backup_type'] ?? 'local',
            'retention_days' => (int)($_POST['retention_days'] ?? 30),
            
            // SMB settings
            'smb_enabled' => !empty($_POST['smb_enabled']),
            'smb_host' => trim($_POST['smb_host'] ?? ''),
            'smb_share' => trim($_POST['smb_share'] ?? ''),
            'smb_path' => trim($_POST['smb_path'] ?? ''),
            'smb_username' => trim($_POST['smb_username'] ?? ''),
            'smb_workgroup' => trim($_POST['smb_workgroup'] ?? ''),
            'smb_binary' => trim($_POST['smb_binary'] ?? '/usr/bin/smbclient'),
            
            // FTP settings
            'ftp_enabled' => !empty($_POST['ftp_enabled']),
            'ftp_host' => trim($_POST['ftp_host'] ?? ''),
            'ftp_port' => (int)($_POST['ftp_port'] ?? 21),
            'ftp_username' => trim($_POST['ftp_username'] ?? ''),
            'ftp_path' => trim($_POST['ftp_path'] ?? ''),
            'ftp_passive' => !empty($_POST['ftp_passive']),
        ];

        // Handle passwords - only update if new value provided
        $smbPassword = $_POST['smb_password'] ?? '';
        if ($smbPassword !== '' && $smbPassword !== '********') {
            $data['smb_password'] = $smbPassword;
        } else {
            // Keep existing password
            $data['smb_password'] = $currentSettings['smb_password_decrypted'] ?? '';
        }

        $ftpPassword = $_POST['ftp_password'] ?? '';
        if ($ftpPassword !== '' && $ftpPassword !== '********') {
            $data['ftp_password'] = $ftpPassword;
        } else {
            // Keep existing password
            $data['ftp_password'] = $currentSettings['ftp_password_decrypted'] ?? '';
        }

        // Validate based on backup type
        if ($data['backup_type'] === 'smb' && $data['smb_enabled']) {
            if (empty($data['smb_host']) || empty($data['smb_share']) || empty($data['smb_username'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Untuk backup SMB, Host, Share, dan Username wajib diisi.']);
                return;
            }
        }

        if ($data['backup_type'] === 'ftp' && $data['ftp_enabled']) {
            if (empty($data['ftp_host']) || empty($data['ftp_username'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Untuk backup FTP, Host dan Username wajib diisi.']);
                return;
            }
        }

        // Validate retention days
        if ($data['retention_days'] < 1 || $data['retention_days'] > 365) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Retensi backup harus antara 1-365 hari.']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $result = BackupSettingsHelper::saveSettings($db, $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Pengaturan backup berhasil disimpan.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pengaturan backup.']);
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Test SMB connection via AJAX
     */
    public function testSmb()
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if (!in_array($role, ['superadmin', 'kombi'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            return;
        }

        header('Content-Type: application/json');

        $config = [
            'host' => trim($_POST['host'] ?? ''),
            'share' => trim($_POST['share'] ?? ''),
            'path' => trim($_POST['path'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'workgroup' => trim($_POST['workgroup'] ?? ''),
            'binary' => trim($_POST['binary'] ?? '/usr/bin/smbclient'),
        ];

        $result = BackupSettingsHelper::testSmbConnection($config);
        echo json_encode($result);
    }

    /**
     * Test FTP connection via AJAX
     */
    public function testFtp()
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if (!in_array($role, ['superadmin', 'kombi'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            return;
        }

        header('Content-Type: application/json');

        $config = [
            'host' => trim($_POST['host'] ?? ''),
            'port' => (int)($_POST['port'] ?? 21),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'path' => trim($_POST['path'] ?? ''),
            'passive' => !empty($_POST['passive']),
        ];

        $result = BackupSettingsHelper::testFtpConnection($config);
        echo json_encode($result);
    }
}
