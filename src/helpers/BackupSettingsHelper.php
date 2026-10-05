<?php

/**
 * BackupSettingsHelper
 * 
 * Helper untuk mengelola pengaturan backup yang disimpan di database
 * Termasuk pengaturan SMB dan FTP untuk sinkronisasi backup
 */
class BackupSettingsHelper
{
    private const ENCRYPTION_METHOD = 'AES-256-CBC';
    private const ENCRYPTION_KEY_ENV = 'BACKUP_ENCRYPTION_KEY';
    private const DEFAULT_ENCRYPTION_KEY = 'K0mb1B4ckup2024!'; // Fallback key

    /**
     * Get all backup settings from database
     *
     * @param PDO $db Database connection
     * @return array Backup settings
     */
    public static function getSettings(PDO $db): array
    {
        $query = "SELECT * FROM backup_settings LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$settings) {
            // Return default settings if table is empty
            return self::getDefaultSettings();
        }

        // Decrypt passwords
        if (!empty($settings['smb_password'])) {
            $settings['smb_password_decrypted'] = self::decrypt($settings['smb_password']);
        }
        if (!empty($settings['ftp_password'])) {
            $settings['ftp_password_decrypted'] = self::decrypt($settings['ftp_password']);
        }

        return $settings;
    }

    /**
     * Save backup settings to database
     *
     * @param PDO $db Database connection
     * @param array $data Settings data to save
     * @return bool True if successful
     */
    public static function saveSettings(PDO $db, array $data): bool
    {
        // Prepare data
        $backupEnabled = !empty($data['backup_enabled']) ? 1 : 0;
        $backupType = $data['backup_type'] ?? 'local';
        $retentionDays = (int)($data['retention_days'] ?? 30);

        // SMB settings
        $smbEnabled = !empty($data['smb_enabled']) ? 1 : 0;
        $smbHost = $data['smb_host'] ?? null;
        $smbShare = $data['smb_share'] ?? null;
        $smbPath = $data['smb_path'] ?? null;
        $smbUsername = $data['smb_username'] ?? null;
        $smbPassword = !empty($data['smb_password']) ? self::encrypt($data['smb_password']) : null;
        $smbWorkgroup = $data['smb_workgroup'] ?? null;
        $smbBinary = $data['smb_binary'] ?? '/usr/bin/smbclient';

        // FTP settings
        $ftpEnabled = !empty($data['ftp_enabled']) ? 1 : 0;
        $ftpHost = $data['ftp_host'] ?? null;
        $ftpPort = (int)($data['ftp_port'] ?? 21);
        $ftpUsername = $data['ftp_username'] ?? null;
        $ftpPassword = !empty($data['ftp_password']) ? self::encrypt($data['ftp_password']) : null;
        $ftpPath = $data['ftp_path'] ?? null;
        $ftpPassive = isset($data['ftp_passive']) ? 1 : 0;

        // Check if settings exist
        $checkQuery = "SELECT COUNT(*) FROM backup_settings";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn() > 0;

        if ($exists) {
            // Update existing settings
            $query = "UPDATE backup_settings SET
                backup_enabled = :backup_enabled,
                backup_type = :backup_type,
                retention_days = :retention_days,
                smb_enabled = :smb_enabled,
                smb_host = :smb_host,
                smb_share = :smb_share,
                smb_path = :smb_path,
                smb_username = :smb_username,
                smb_password = :smb_password,
                smb_workgroup = :smb_workgroup,
                smb_binary = :smb_binary,
                ftp_enabled = :ftp_enabled,
                ftp_host = :ftp_host,
                ftp_port = :ftp_port,
                ftp_username = :ftp_username,
                ftp_password = :ftp_password,
                ftp_path = :ftp_path,
                ftp_passive = :ftp_passive,
                updated_at = CURRENT_TIMESTAMP
            ";
        } else {
            // Insert new settings
            $query = "INSERT INTO backup_settings (
                backup_enabled, backup_type, retention_days,
                smb_enabled, smb_host, smb_share, smb_path, smb_username, smb_password, smb_workgroup, smb_binary,
                ftp_enabled, ftp_host, ftp_port, ftp_username, ftp_password, ftp_path, ftp_passive
            ) VALUES (
                :backup_enabled, :backup_type, :retention_days,
                :smb_enabled, :smb_host, :smb_share, :smb_path, :smb_username, :smb_password, :smb_workgroup, :smb_binary,
                :ftp_enabled, :ftp_host, :ftp_port, :ftp_username, :ftp_password, :ftp_path, :ftp_passive
            )";
        }

        $stmt = $db->prepare($query);
        
        $params = [
            ':backup_enabled' => $backupEnabled,
            ':backup_type' => $backupType,
            ':retention_days' => $retentionDays,
            ':smb_enabled' => $smbEnabled,
            ':smb_host' => $smbHost,
            ':smb_share' => $smbShare,
            ':smb_path' => $smbPath,
            ':smb_username' => $smbUsername,
            ':smb_password' => $smbPassword,
            ':smb_workgroup' => $smbWorkgroup,
            ':smb_binary' => $smbBinary,
            ':ftp_enabled' => $ftpEnabled,
            ':ftp_host' => $ftpHost,
            ':ftp_port' => $ftpPort,
            ':ftp_username' => $ftpUsername,
            ':ftp_password' => $ftpPassword,
            ':ftp_path' => $ftpPath,
            ':ftp_passive' => $ftpPassive,
        ];

        return $stmt->execute($params);
    }

    /**
     * Get backup settings in format compatible with BackupManager
     *
     * @param PDO $db Database connection
     * @return array Backup config array
     */
    public static function getBackupConfig(PDO $db): array
    {
        $settings = self::getSettings($db);

        // Determine external target based on settings
        $externalTarget = null;

        if (!$settings['backup_enabled']) {
            $externalTarget = null;
        } elseif ($settings['backup_type'] === 'smb' && $settings['smb_enabled']) {
            $externalTarget = [
                'type' => 'smb',
                'host' => $settings['smb_host'],
                'share' => $settings['smb_share'],
                'path' => $settings['smb_path'],
                'username' => $settings['smb_username'],
                'password' => $settings['smb_password_decrypted'] ?? null,
                'workgroup' => $settings['smb_workgroup'],
                'binary' => $settings['smb_binary'],
            ];
        } elseif ($settings['backup_type'] === 'ftp' && $settings['ftp_enabled']) {
            $externalTarget = [
                'type' => 'ftp',
                'host' => $settings['ftp_host'],
                'port' => $settings['ftp_port'],
                'username' => $settings['ftp_username'],
                'password' => $settings['ftp_password_decrypted'] ?? null,
                'path' => $settings['ftp_path'],
                'passive' => $settings['ftp_passive'],
            ];
        }

        return [
            'storage_path' => STORAGE_PATH . '/backups',
            'external_target' => $externalTarget,
            'retention_days' => $settings['retention_days'],
        ];
    }

    /**
     * Encrypt a string using AES-256-CBC
     *
     * @param string $data Data to encrypt
     * @return string Encrypted data (base64 encoded)
     */
    private static function encrypt(string $data): string
    {
        $key = self::getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ENCRYPTION_METHOD));
        $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt a string encrypted with encrypt()
     *
     * @param string $data Encrypted data
     * @return string Decrypted data
     */
    private static function decrypt(string $data): string
    {
        $key = self::getEncryptionKey();
        $data = base64_decode($data);
        list($encrypted_data, $iv) = explode('::', $data, 2);
        return openssl_decrypt($encrypted_data, self::ENCRYPTION_METHOD, $key, 0, $iv);
    }

    /**
     * Get encryption key from environment or use default
     *
     * @return string Encryption key
     */
    private static function getEncryptionKey(): string
    {
        $key = getenv(self::ENCRYPTION_KEY_ENV);
        if (!$key) {
            $key = self::DEFAULT_ENCRYPTION_KEY;
        }
        // Ensure key is exactly 32 bytes for AES-256
        return hash('sha256', $key, true);
    }

    /**
     * Get default settings
     *
     * @return array Default settings
     */
    private static function getDefaultSettings(): array
    {
        return [
            'id' => null,
            'backup_enabled' => 1,
            'backup_type' => 'local',
            'retention_days' => 30,
            'smb_enabled' => 0,
            'smb_host' => null,
            'smb_share' => null,
            'smb_path' => null,
            'smb_username' => null,
            'smb_password' => null,
            'smb_password_decrypted' => null,
            'smb_workgroup' => null,
            'smb_binary' => '/usr/bin/smbclient',
            'ftp_enabled' => 0,
            'ftp_host' => null,
            'ftp_port' => 21,
            'ftp_username' => null,
            'ftp_password' => null,
            'ftp_password_decrypted' => null,
            'ftp_path' => null,
            'ftp_passive' => 1,
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    /**
     * Test SMB connection
     *
     * @param array $config SMB configuration
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testSmbConnection(array $config): array
    {
        $required = ['host', 'share', 'username'];
        foreach ($required as $key) {
            if (empty($config[$key])) {
                return ['success' => false, 'message' => "Parameter '$key' wajib diisi."];
            }
        }

        $binary = $config['binary'] ?? '/usr/bin/smbclient';
        
        // Check if smbclient exists
        if (!file_exists($binary) || !is_executable($binary)) {
            return ['success' => false, 'message' => "smbclient tidak ditemukan di: $binary"];
        }

        $host = $config['host'];
        $share = $config['share'];
        $username = $config['username'];
        $password = $config['password'] ?? '';
        $workgroup = $config['workgroup'] ?? null;

        // Build smbclient command for testing (just list directory)
        $shareUrl = sprintf('//%s/%s', $host, $share);
        $command = sprintf('%s %s -U %s%%%s -c "ls"',
            escapeshellarg($binary),
            escapeshellarg($shareUrl),
            escapeshellarg($username),
            escapeshellarg($password)
        );

        if ($workgroup) {
            $command = sprintf('%s %s -W %s -U %s%%%s -c "ls"',
                escapeshellarg($binary),
                escapeshellarg($shareUrl),
                escapeshellarg($workgroup),
                escapeshellarg($username),
                escapeshellarg($password)
            );
        }

        $descriptor = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($command, $descriptor, $pipes);

        if (!is_resource($process)) {
            return ['success' => false, 'message' => 'Gagal menjalankan smbclient.'];
        }

        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode === 0) {
            return ['success' => true, 'message' => 'Koneksi SMB berhasil!'];
        } else {
            $errorMsg = trim($errorOutput ?: $output ?: 'Koneksi gagal.');
            return ['success' => false, 'message' => "Koneksi gagal: $errorMsg"];
        }
    }

    /**
     * Test FTP connection
     *
     * @param array $config FTP configuration
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testFtpConnection(array $config): array
    {
        $required = ['host', 'username'];
        foreach ($required as $key) {
            if (empty($config[$key])) {
                return ['success' => false, 'message' => "Parameter '$key' wajib diisi."];
            }
        }

        $host = $config['host'];
        $port = (int)($config['port'] ?? 21);
        $username = $config['username'];
        $password = $config['password'] ?? '';
        $passive = (bool)($config['passive'] ?? true);

        // Test FTP connection
        $conn = @ftp_connect($host, $port, 10);
        if (!$conn) {
            return ['success' => false, 'message' => "Tidak dapat terhubung ke $host:$port"];
        }

        $loginResult = @ftp_login($conn, $username, $password);
        if (!$loginResult) {
            ftp_close($conn);
            return ['success' => false, 'message' => 'Login FTP gagal. Periksa username/password.'];
        }

        if ($passive) {
            ftp_pasv($conn, true);
        }

        // Get current directory to verify connection
        $cwd = @ftp_pwd($conn);
        ftp_close($conn);

        if ($cwd === false) {
            return ['success' => false, 'message' => 'Koneksi berhasil tapi gagal mendapatkan direktori saat ini.'];
        }

        return ['success' => true, 'message' => "Koneksi FTP berhasil! Direktori saat ini: $cwd"];
    }
}
