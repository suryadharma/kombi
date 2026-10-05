<?php

require_once __DIR__ . '/BackupSettingsHelper.php';

class BackupManager
{
    private string $storagePath;
    private ?string $externalTargetPath = null;
    private ?array $externalTargetSmb = null;
    private string $externalTargetSmbBinary = 'smbclient';
    private ?string $externalTargetLabel = null;
    private int $retentionDays;
    private ?array $externalTargetFtp = null;
    private string $externalTargetFtpBinary = 'ftp';
    private ?PDO $db = null;

    public function __construct()
    {
        // Try to get config from database first, fallback to .env config file
        $config = $this->loadConfigFromDatabase();
        
        if ($config === null) {
            // Fallback to config file
            $config = require CONFIG_PATH . '/backup.php';
        }

        $this->storagePath = $config['storage_path'] ?? (STORAGE_PATH . '/backups');
        $this->retentionDays = (int)($config['retention_days'] ?? 30);
        $externalTarget = $config['external_target'] ?? null;

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }

        if (is_string($externalTarget) && $externalTarget !== '') {
            $this->externalTargetPath = $externalTarget;
            $this->externalTargetLabel = $externalTarget;
            if (!is_dir($this->externalTargetPath)) {
                @mkdir($this->externalTargetPath, 0775, true);
            }
        } elseif (is_array($externalTarget) && ($externalTarget['type'] ?? null) === 'smb') {
            $this->externalTargetSmb = $externalTarget;
            if (!empty($externalTarget['binary'])) {
                $binary = trim((string)$externalTarget['binary']);
                if ($binary !== '') {
                    $this->externalTargetSmbBinary = $binary;
                }
            }
            if (!isset($this->externalTargetSmb['binary']) || $this->externalTargetSmb['binary'] === '') {
                $this->externalTargetSmb['binary'] = $this->externalTargetSmbBinary;
            }
            $this->externalTargetLabel = $this->buildSmbLabel($externalTarget);
        } elseif (is_array($externalTarget) && ($externalTarget['type'] ?? null) === 'ftp') {
            $this->externalTargetFtp = $externalTarget;
            $this->externalTargetLabel = $this->buildFtpLabel($externalTarget);
        }
    }

    /**
     * Try to load backup configuration from database
     * Returns null if database is not available or table doesn't exist
     */
    private function loadConfigFromDatabase(): ?array
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            
            // Check if table exists
            $checkQuery = "SHOW TABLES LIKE 'backup_settings'";
            $stmt = $this->db->query($checkQuery);
            if ($stmt->rowCount() === 0) {
                return null; // Table doesn't exist
            }
            
            // Get settings from database
            return BackupSettingsHelper::getBackupConfig($this->db);
        } catch (Throwable $e) {
            // Database not available or other error, fallback to config file
            return null;
        }
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function getExternalTarget(): ?string
    {
        return $this->externalTargetLabel;
    }

    public function listBackups(): array
    {
        $files = glob($this->storagePath . '/*.tar.gz');
        if (!$files) {
            return [];
        }

        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified_at' => filemtime($file),
                'path' => realpath($file),
            ];
        }

        usort($backups, static function ($a, $b) {
            return $b['modified_at'] <=> $a['modified_at'];
        });

        return $backups;
    }

    public function createBackup(bool $includeUploads = true): array
    {
        $timestamp = date('Ymd-His');
        $prefix = 'kombi-backup-' . $timestamp;

        $workDir = $this->storagePath . '/tmp-' . $prefix;
        if (!mkdir($workDir, 0775, true) && !is_dir($workDir)) {
            throw new RuntimeException('Gagal membuat direktori kerja sementara.');
        }

        $databaseDump = $workDir . '/database.sql';
        $uploadsTarget = $workDir . '/uploads';

        $warnings = [];

        try {
            $this->dumpDatabase($databaseDump);

            if ($includeUploads) {
                $this->copyUploads($uploadsTarget);
            }

            $archivePath = $this->storagePath . '/' . $prefix . '.tar.gz';
            $this->createArchive($workDir, $archivePath);

            try {
                $this->mirrorBackup($archivePath);
            } catch (Throwable $mirrorError) {
                $warnings[] = $mirrorError->getMessage();
                $this->debug('mirror warning', [
                    'archive' => $archivePath,
                    'message' => $mirrorError->getMessage(),
                ]);
            }

            $this->cleanupOldBackups();

            return [
                'name' => basename($archivePath),
                'path' => $archivePath,
                'size' => filesize($archivePath),
                'warnings' => $warnings,
            ];
        } finally {
            $this->deleteDirectory($workDir);
        }
    }

    private function mirrorBackup(string $archivePath): void
    {
        if ($this->externalTargetPath && is_dir($this->externalTargetPath)) {
            @copy($archivePath, $this->externalTargetPath . '/' . basename($archivePath));
            return;
        }

        if ($this->externalTargetSmb) {
            $this->copyToSmb($archivePath, $this->externalTargetSmb);
            return;
        }

        if ($this->externalTargetFtp) {
            $this->copyToFtp($archivePath, $this->externalTargetFtp);
            return;
        }
    }

    public function deleteBackup(string $filename): void
    {
        $path = realpath($this->storagePath . '/' . $filename);
        $this->ensureInsideStorage($path);

        if ($path && is_file($path)) {
            unlink($path);
        }
    }

    public function restoreBackup(string $filename, bool $restoreUploads = true): void
    {
        $archivePath = realpath($this->storagePath . '/' . $filename);
        $this->ensureInsideStorage($archivePath);

        if (!$archivePath || !is_file($archivePath)) {
            throw new RuntimeException('Berkas cadangan tidak ditemukan.');
        }

        $tmpDir = $this->storagePath . '/restore-' . uniqid('', true);
        if (!mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
            throw new RuntimeException('Gagal membuat direktori ekstraksi.');
        }

        try {
            $this->extractArchive($archivePath, $tmpDir);
            $sqlPath = $tmpDir . '/database.sql';

            if (!is_file($sqlPath)) {
                throw new RuntimeException('Berkas database.sql tidak ditemukan di cadangan.');
            }

            $this->restoreDatabase($sqlPath);

            if ($restoreUploads) {
                $uploadsSource = $tmpDir . '/uploads';
                if (is_dir($uploadsSource)) {
                    $this->syncUploads($uploadsSource);
                }
            }
        } finally {
            $this->deleteDirectory($tmpDir);
        }
    }

    public function getBackupPath(string $filename): ?string
    {
        $path = realpath($this->storagePath . '/' . $filename);
        $this->ensureInsideStorage($path);
        return $path;
    }

    /**
     * Force re-sync all local backups to the configured SMB target.
     *
     * @return array{uploaded: string[], failed: array<string,string>} Summary of sync result.
     */
    public function syncAllBackupsToSmb(): array
    {
        $uploaded = [];
        $failed = [];

        if (!$this->externalTargetSmb) {
            $this->debug('syncAllBackupsToSmb skipped: SMB target not configured');
            return ['uploaded' => [], 'failed' => ['__global' => 'SMB target tidak dikonfigurasi.']];
        }

        foreach (glob($this->storagePath . '/kombi-backup-*.tar.gz') as $file) {
            try {
                $this->copyToSmb($file, $this->externalTargetSmb);
                $uploaded[] = basename($file);
            } catch (Throwable $e) {
                $failed[basename($file)] = $e->getMessage();
                $this->debug('syncAllBackupsToSmb failed', [
                    'archive' => $file,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return ['uploaded' => $uploaded, 'failed' => $failed];
    }

    private function copyToSmb(string $archivePath, array $config): void
    {
        foreach (['host', 'share', 'username'] as $requiredKey) {
            if (empty($config[$requiredKey])) {
                throw new RuntimeException(sprintf('Konfigurasi SMB "%s" belum diatur.', $requiredKey));
            }
        }

        $binary = $config['binary'] ?? $this->externalTargetSmbBinary;
        $this->debug('copyToSmb invoked', [
            'binary' => $binary,
            'is_file' => is_file($binary) ? 'yes' : 'no',
            'is_executable' => is_executable($binary) ? 'yes' : 'no',
        ]);

        if (strpos($binary, DIRECTORY_SEPARATOR) !== false) {
            if (!is_file($binary) || !is_executable($binary)) {
                $this->debug('binary check failed (absolute)', ['binary' => $binary]);
                throw new RuntimeException('Perintah smbclient tidak ditemukan di server. Pasang paket smbclient untuk menggunakan fitur mirror SMB.');
            }
        } elseif (!$this->commandExists($binary)) {
            $this->debug('binary check failed (command)', ['binary' => $binary]);
            throw new RuntimeException('Perintah smbclient tidak ditemukan di server. Pasang paket smbclient untuk menggunakan fitur mirror SMB.');
        }

        $host = (string)$config['host'];
        $share = trim((string)$config['share'], '/');
        $remotePath = trim((string)($config['path'] ?? ''), '/');
        $username = (string)$config['username'];
        $password = (string)($config['password'] ?? '');
        $workgroup = isset($config['workgroup']) ? trim((string)$config['workgroup']) : null;

        $shareUrl = sprintf('//%s/%s', $host, $share);
        $commands = [];

        if ($remotePath !== '') {
            $segments = array_filter(explode('/', $remotePath), static fn ($segment) => $segment !== '');
            foreach ($segments as $segment) {
                $escaped = $this->escapeForSmb($segment);
                $commands[] = sprintf('mkdir "%s"', $escaped);
                $commands[] = sprintf('cd "%s"', $escaped);
            }
        }

        $commands[] = sprintf(
            'put "%s" "%s"',
            $this->escapeForSmb($archivePath),
            $this->escapeForSmb(basename($archivePath))
        );

        $commandString = implode('; ', $commands);
        $smbclientCommand = $this->buildSmbClientCommand(
            $binary,
            $shareUrl,
            $username,
            $password,
            $commandString,
            $workgroup
        );

        $descriptor = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($smbclientCommand, $descriptor, $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Gagal menjalankan smbclient untuk mengirim cadangan.');
        }

        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            $message = trim($errorOutput ?: $output ?: 'Tidak diketahui.');
            throw new RuntimeException('Gagal mengirim cadangan ke SMB: ' . $message);
        }

        $this->debug('copyToSmb completed', [
            'binary_used' => $binary,
            'archive' => $archivePath,
        ]);
    }

    private function commandExists(string $command): bool
    {
        $command = trim($command);
        if ($command === '') {
            return false;
        }

        if (strpos($command, DIRECTORY_SEPARATOR) !== false) {
            return is_file($command) && is_executable($command);
        }

        $process = proc_open(
            'command -v ' . escapeshellarg($command),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return false;
        }

        // Drain pipes to avoid blocking or non-zero exit codes caused by unread output.
        if (is_resource($pipes[1])) {
            stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        }
        if (is_resource($pipes[2])) {
            stream_get_contents($pipes[2]);
            fclose($pipes[2]);
        }

        return proc_close($process) === 0;
    }

    private function escapeForSmb(string $value): string
    {
        return str_replace(['"', '\\'], ['\\"', '\\\\'], $value);
    }

    private function buildSmbLabel(array $config): string
    {
        $host = $config['host'] ?? '';
        $share = $config['share'] ?? '';
        $path = trim((string)($config['path'] ?? ''), '/');

        $uri = sprintf('smb://%s/%s', $host, trim($share, '/'));
        if ($path !== '') {
            $uri .= '/' . $path;
        }

        return $uri;
    }

    private function buildSmbClientCommand(
        string $binary,
        string $shareUrl,
        string $username,
        string $password,
        string $commandString,
        ?string $workgroup
    ): string {
        $parts = [
            escapeshellarg($shareUrl),
            '-U',
            escapeshellarg($username . '%' . $password),
            '-c',
            escapeshellarg($commandString),
        ];

        if ($workgroup) {
            array_splice($parts, 2, 0, ['-W', escapeshellarg($workgroup)]);
        }

        return $binary . ' ' . implode(' ', $parts);
    }

    /**
     * Build FTP label for display
     */
    private function buildFtpLabel(array $config): string
    {
        $host = $config['host'] ?? '';
        $port = $config['port'] ?? 21;
        $path = trim((string)($config['path'] ?? ''), '/');

        $uri = sprintf('ftp://%s:%d', $host, $port);
        if ($path !== '') {
            $uri .= '/' . $path;
        }

        return $uri;
    }

    /**
     * Copy backup file to FTP server
     */
    private function copyToFtp(string $archivePath, array $config): void
    {
        foreach (['host', 'username'] as $requiredKey) {
            if (empty($config[$requiredKey])) {
                throw new RuntimeException(sprintf('Konfigurasi FTP "%s" belum diatur.', $requiredKey));
            }
        }

        $host = $config['host'];
        $port = (int)($config['port'] ?? 21);
        $username = $config['username'];
        $password = $config['password'] ?? '';
        $remotePath = $config['path'] ?? '/';
        $passive = (bool)($config['passive'] ?? true);

        $this->debug('copyToFtp invoked', [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'passive' => $passive,
            'archive' => $archivePath,
        ]);

        // Connect to FTP server
        $conn = @ftp_connect($host, $port, 30);
        if (!$conn) {
            throw new RuntimeException(sprintf('Tidak dapat terhubung ke server FTP %s:%d.', $host, $port));
        }

        try {
            // Login
            $loginResult = @ftp_login($conn, $username, $password);
            if (!$loginResult) {
                throw new RuntimeException('Login FTP gagal. Periksa username dan password.');
            }

            // Set passive mode
            if ($passive) {
                ftp_pasv($conn, true);
            }

            // Create remote directory if needed
            $this->ftpMakeDirectory($conn, $remotePath);

            // Change to remote directory
            if (!@ftp_chdir($conn, $remotePath)) {
                throw new RuntimeException(sprintf('Tidak dapat mengakses direktori FTP: %s', $remotePath));
            }

            // Upload file
            $remoteFile = basename($archivePath);
            $uploadResult = @ftp_put($conn, $remoteFile, $archivePath, FTP_BINARY);
            if (!$uploadResult) {
                throw new RuntimeException('Gagal mengunggah file ke server FTP.');
            }

            $this->debug('copyToFtp completed', [
                'remote_file' => $remoteFile,
                'size' => filesize($archivePath),
            ]);
        } finally {
            ftp_close($conn);
        }
    }

    /**
     * Create directory on FTP server recursively
     */
    private function ftpMakeDirectory($conn, string $path): void
    {
        $path = trim($path, '/');
        if ($path === '') {
            return;
        }

        $segments = explode('/', $path);
        $currentPath = '';

        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }

            $currentPath .= '/' . $segment;

            // Try to change to directory, if fails, create it
            if (!@ftp_chdir($conn, $currentPath)) {
                // Go back to root
                @ftp_chdir($conn, '/');

                // Try to create directory
                if (!@ftp_mkdir($conn, $currentPath)) {
                    // Directory might already exist, try to change to it
                    if (!@ftp_chdir($conn, $currentPath)) {
                        throw new RuntimeException(sprintf('Tidak dapat membuat direktori FTP: %s', $currentPath));
                    }
                }
            }

            // Go back to root for next iteration
            @ftp_chdir($conn, '/');
        }
    }

    /**
     * Force re-sync all local backups to the configured FTP target.
     *
     * @return array{uploaded: string[], failed: array<string,string>} Summary of sync result.
     */
    public function syncAllBackupsToFtp(): array
    {
        $uploaded = [];
        $failed = [];

        if (!$this->externalTargetFtp) {
            $this->debug('syncAllBackupsToFtp skipped: FTP target not configured');
            return ['uploaded' => [], 'failed' => ['__global' => 'FTP target tidak dikonfigurasi.']];
        }

        foreach (glob($this->storagePath . '/kombi-backup-*.tar.gz') as $file) {
            try {
                $this->copyToFtp($file, $this->externalTargetFtp);
                $uploaded[] = basename($file);
            } catch (Throwable $e) {
                $failed[basename($file)] = $e->getMessage();
                $this->debug('syncAllBackupsToFtp failed', [
                    'archive' => $file,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return ['uploaded' => $uploaded, 'failed' => $failed];
    }

    private function debug(string $message, array $context = []): void
    {
        try {
            $logDir = STORAGE_PATH . '/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0775, true);
            }
            $line = sprintf(
                "[%s] %s %s\n",
                date('c'),
                $message,
                $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : ''
            );
            @file_put_contents($logDir . '/backup_debug.log', $line, FILE_APPEND);
        } catch (Throwable $e) {
            // swallow logging errors
        }
    }

    private function dumpDatabase(string $targetPath): void
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->exec("SET NAMES utf8mb4");

        $handle = fopen($targetPath, 'w');
        if (!$handle) {
            throw new RuntimeException('Gagal membuat file dump database.');
        }

        $header = sprintf(
            "-- KomBi backup\n-- Generated at %s\n-- Host: %s\n\n",
            date(DATE_ATOM),
            gethostname() ?: 'unknown-host'
        );
        fwrite($handle, $header);
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $this->dumpTable($pdo, $handle, $table);
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function dumpTable(PDO $pdo, $handle, string $table): void
    {
        $escapedTable = $this->escapeIdentifier($table);
        fwrite($handle, "\n-- -------------------------------------------------------\n");
        fwrite($handle, sprintf("-- Table structure for %s\n", $escapedTable));
        fwrite($handle, sprintf("DROP TABLE IF EXISTS %s;\n", $escapedTable));

        $createStmt = $pdo->query(sprintf('SHOW CREATE TABLE %s', $escapedTable))->fetch(PDO::FETCH_ASSOC);
        $createSql = $createStmt['Create Table'] ?? null;
        if (!$createSql) {
            return;
        }
        fwrite($handle, $createSql . ";\n\n");

        fwrite($handle, sprintf("-- Dumping data for table %s\n", $escapedTable));
        $columns = $this->getColumnNames($pdo, $table);
        $stmt = $pdo->query(sprintf('SELECT * FROM %s', $escapedTable));
        $rowCount = 0;
        $batchValues = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batchValues[] = $this->formatRowForInsert($pdo, $row);
            $rowCount++;

            if (count($batchValues) >= 200) {
                $this->writeInsertBatch($handle, $escapedTable, $columns, $batchValues);
                $batchValues = [];
            }
        }

        if (!empty($batchValues)) {
            $this->writeInsertBatch($handle, $escapedTable, $columns, $batchValues);
        }

        if ($rowCount === 0) {
            fwrite($handle, "-- (no rows)\n");
        }
    }

    private function formatRowForInsert(PDO $pdo, array $row): array
    {
        $values = [];
        foreach ($row as $value) {
            if ($value === null) {
                $values[] = 'NULL';
                continue;
            }
            $values[] = $pdo->quote($value);
        }
        return $values;
    }

    private function getColumnNames(PDO $pdo, string $table): array
    {
        $stmt = $pdo->query(sprintf('SHOW COLUMNS FROM %s', $this->escapeIdentifier($table)));
        $columns = [];
        while ($column = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $column['Field'];
        }
        return $columns;
    }

    private function writeInsertBatch($handle, string $table, array $columns, array $batchValues): void
    {
        $columnsSql = implode(', ', array_map([$this, 'escapeIdentifier'], $columns));

        $valuesSql = array_map(function ($rowValues) {
            return '(' . implode(', ', $rowValues) . ')';
        }, $batchValues);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES\n%s;\n",
            $table,
            $columnsSql,
            implode(",\n", $valuesSql)
        );
        fwrite($handle, $sql);
    }

    private function escapeIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function restoreDatabase(string $sqlPath): void
    {
        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->exec("SET NAMES utf8mb4");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

        $statement = '';
        $handle = fopen($sqlPath, 'r');
        if (!$handle) {
            throw new RuntimeException('Tidak dapat membaca berkas cadangan.');
        }

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $statement .= $line;
            if (substr(rtrim($statement), -1) === ';') {
                $this->executeStatement($pdo, $statement);
                $statement = '';
            }
        }

        fclose($handle);
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }

    private function executeStatement(PDO $pdo, string $statement): void
    {
        try {
            $pdo->exec($statement);
        } catch (Throwable $e) {
            throw new RuntimeException('Kesalahan saat mengeksekusi pernyataan SQL: ' . $e->getMessage());
        }
    }

    private function copyUploads(string $targetDir): void
    {
        $uploadsDir = PUBLIC_PATH . '/uploads';
        if (!is_dir($uploadsDir)) {
            return;
        }

        $this->recursiveCopy($uploadsDir, $targetDir);
    }

    private function syncUploads(string $sourceDir): void
    {
        $uploadsDir = PUBLIC_PATH . '/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0775, true);
        }

        $this->recursiveCopy($sourceDir, $uploadsDir);
    }

    private function createArchive(string $sourceDir, string $archivePath): void
    {
        $this->assertCommandExists('tar');

        $command = sprintf(
            'cd %s && tar -czf %s .',
            escapeshellarg($sourceDir),
            escapeshellarg($archivePath)
        );

        exec($command, $output, $status);
        if ($status !== 0) {
            throw new RuntimeException('Gagal membuat arsip cadangan: ' . implode("\n", $output));
        }
    }

    private function extractArchive(string $archivePath, string $destination): void
    {
        $this->assertCommandExists('tar');

        $command = sprintf(
            'tar -xzf %s -C %s',
            escapeshellarg($archivePath),
            escapeshellarg($destination)
        );

        exec($command, $output, $status);
        if ($status !== 0) {
            throw new RuntimeException('Gagal mengekstrak arsip cadangan: ' . implode("\n", $output));
        }
    }

    private function recursiveCopy(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0775, true);
                }
            } else {
                if (!is_dir(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0775, true);
                }
                copy($item->getPathname(), $targetPath);
            }
        }
    }

    private function cleanupOldBackups(): void
    {
        if ($this->retentionDays <= 0) {
            return;
        }

        $threshold = time() - ($this->retentionDays * 86400);
        foreach (glob($this->storagePath . '/kombi-backup-*.tar.gz') as $file) {
            if (filemtime($file) < $threshold) {
                @unlink($file);
            }
        }

        if ($this->externalTargetPath && is_dir($this->externalTargetPath)) {
            foreach (glob($this->externalTargetPath . '/kombi-backup-*.tar.gz') as $file) {
                if (filemtime($file) < $threshold) {
                    @unlink($file);
                }
            }
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
    }

    private function ensureInsideStorage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $normalizedStorage = realpath($this->storagePath);
        if (!$normalizedStorage || strpos($path, $normalizedStorage) !== 0) {
            throw new RuntimeException('Akses cadangan di luar direktori penyimpanan ditolak.');
        }
    }

    private function assertCommandExists(string $command): void
    {
        $check = sprintf('command -v %s', escapeshellarg($command));
        exec($check, $output, $status);
        if ($status !== 0) {
            throw new RuntimeException(sprintf('Perintah "%s" tidak tersedia di server.', $command));
        }
    }
}
