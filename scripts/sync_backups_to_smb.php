#!/usr/bin/env php
<?php

define('BASE_PATH', realpath(__DIR__ . '/../src'));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('CONFIG_PATH', BASE_PATH . '/config');
define('HELPER_PATH', BASE_PATH . '/helpers');
define('STORAGE_PATH', BASE_PATH . '/storage');

require CONFIG_PATH . '/database.php';
require HELPER_PATH . '/BackupManager.php';

$manager = new BackupManager();
$result = $manager->syncAllBackupsToSmb();

if (!empty($result['uploaded'])) {
    echo "[OK] Berhasil mengunggah ke SMB:\n";
    foreach ($result['uploaded'] as $name) {
        echo "  - {$name}\n";
    }
} else {
    echo "[INFO] Tidak ada backup lokal yang diunggah ulang.\n";
}

if (!empty($result['failed'])) {
    echo "[WARN] Gagal mengunggah:\n";
    foreach ($result['failed'] as $name => $message) {
        echo "  - {$name}: {$message}\n";
    }
}
