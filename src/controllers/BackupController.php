<?php

class BackupController extends BaseController
{
    private function enforceSuperadmin(): void
    {
        $this->requireAuth();
        if ($this->getUserRole() !== 'superadmin') {
            $this->redirect('/dashboard');
        }
    }

    public function index(): void
    {
        $this->enforceSuperadmin();

        $manager = new BackupManager();
        $backups = $manager->listBackups();

        $this->render('backups/index', [
            'backups' => $backups,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
            'storagePath' => $manager->getStoragePath(),
            'externalTarget' => $manager->getExternalTarget(),
        ]);
    }

    public function create(): void
    {
        $this->enforceSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/backups');
            return;
        }

        try {
            $manager = new BackupManager();
            $result = $manager->createBackup(true);
            $warnings = $result['warnings'] ?? [];

            $message = 'Cadangan berhasil dibuat.';
            if (!empty($warnings)) {
                $message .= ' (Catatan: ' . implode(' | ', $warnings) . ')';
            }

            $this->setFlash('success', $message);
            $this->clearFlash('error');
        } catch (Throwable $e) {
            error_log('[BackupController] create backup failed: ' . $e->getMessage());
            $this->setFlash('error', 'Gagal membuat cadangan: ' . $e->getMessage());
        }

        $this->redirect('/backups');
    }

    public function restore(): void
    {
        $this->enforceSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/backups');
            return;
        }

        $restoreUploads = isset($_POST['restore_uploads']) && $_POST['restore_uploads'] === '1';
        $filename = '';
        
        // Check if file is being uploaded
        if (isset($_FILES['backup_upload']) && $_FILES['backup_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadedFile = $_FILES['backup_upload'];
            $tmpPath = $uploadedFile['tmp_name'];
            $originalName = $uploadedFile['name'];
            
            // Validate file extension
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['gz', 'tgz']) && !str_ends_with(strtolower($originalName), '.tar.gz')) {
                $this->setFlash('error', 'Format file tidak valid. Gunakan file .tar.gz atau .tgz.');
                $this->redirect('/backups');
                return;
            }
            
            // Validate file size (max 100 MB)
            $maxSize = 100 * 1024 * 1024; // 100 MB
            if ($uploadedFile['size'] > $maxSize) {
                $this->setFlash('error', 'Ukuran file terlalu besar. Maksimal 100 MB.');
                $this->redirect('/backups');
                return;
            }
            
            // Generate unique filename
            $filename = 'uploaded-' . date('Y-m-d-His') . '-' . basename($originalName);
            
            // Move uploaded file to storage
            $manager = new BackupManager();
            $storagePath = $manager->getStoragePath();
            $destination = $storagePath . '/' . $filename;
            
            if (!move_uploaded_file($tmpPath, $destination)) {
                $this->setFlash('error', 'Gagal mengupload file.');
                $this->redirect('/backups');
                return;
            }
        } else {
            // Use existing backup file
            $filename = $_POST['backup_file'] ?? '';
            
            if ($filename === '') {
                $this->setFlash('error', 'Pilih berkas cadangan yang akan dipulihkan.');
                $this->redirect('/backups');
                return;
            }
        }

        try {
            $manager = new BackupManager();
            $manager->restoreBackup($filename, $restoreUploads);
            $this->setFlash('success', 'Cadangan berhasil dipulihkan.');
        } catch (Throwable $e) {
            $this->setFlash('error', 'Gagal memulihkan cadangan: ' . $e->getMessage());
        }

        $this->redirect('/backups');
    }

    public function download(array $params): void
    {
        $this->enforceSuperadmin();

        $filename = $params['id'] ?? ($params[0] ?? null);
        if (!$filename) {
            $this->setFlash('error', 'Nama berkas tidak valid.');
            $this->redirect('/backups');
            return;
        }

        $manager = new BackupManager();
        $path = $manager->getBackupPath($filename);

        if (!$path || !is_file($path)) {
            $this->setFlash('error', 'Berkas cadangan tidak ditemukan.');
            $this->redirect('/backups');
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        readfile($path);
        exit();
    }

    public function delete(): void
    {
        $this->enforceSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/backups');
            return;
        }

        $filename = $_POST['backup_file'] ?? '';
        if ($filename === '') {
            $this->setFlash('error', 'Nama berkas tidak valid.');
            $this->redirect('/backups');
            return;
        }

        try {
            $manager = new BackupManager();
            $manager->deleteBackup($filename);
            $this->setFlash('success', 'Cadangan berhasil dihapus.');
        } catch (Throwable $e) {
            $this->setFlash('error', 'Gagal menghapus cadangan: ' . $e->getMessage());
        }

        $this->redirect('/backups');
    }

    public function sync(): void
    {
        $this->enforceSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/backups');
            return;
        }

        try {
            $manager = new BackupManager();
            $result = $manager->syncAllBackupsToSmb();

            $uploaded = $result['uploaded'] ?? [];
            $failed = $result['failed'] ?? [];

            if (!empty($uploaded)) {
                $this->setFlash('success', 'Sinkronisasi SMB berhasil untuk: ' . implode(', ', $uploaded));
            } else {
                $this->setFlash('success', 'Tidak ada cadangan baru yang perlu disinkronkan.');
            }

            if (!empty($failed)) {
                unset($failed['__global']);
                if (!empty($failed)) {
                    $messages = [];
                    foreach ($failed as $file => $message) {
                        $messages[] = "{$file}: {$message}";
                    }
                    $this->setFlash('error', 'Beberapa cadangan gagal disinkronkan: ' . implode(' | ', $messages));
                }
            }
        } catch (Throwable $e) {
            $this->setFlash('error', 'Gagal mengeksekusi sinkronisasi SMB: ' . $e->getMessage());
        }

        $this->redirect('/backups');
    }
}
