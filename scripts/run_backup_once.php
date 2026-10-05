#!/usr/bin/env php
<?php

// Simple one-off CLI runner to trigger BackupManager::createBackup()
// Useful for verifying backup configuration without going through the UI.

define('BASE_PATH', realpath(__DIR__ . '/../src'));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('CONFIG_PATH', BASE_PATH . '/config');
define('HELPER_PATH', BASE_PATH . '/helpers');
define('STORAGE_PATH', BASE_PATH . '/storage');

require CONFIG_PATH . '/database.php';
require HELPER_PATH . '/BackupManager.php';

$includeUploads = true;
if (isset($argv[1]) && $argv[1] === '--skip-uploads') {
    $includeUploads = false;
}

$manager = new BackupManager();
$result = $manager->createBackup($includeUploads);

fwrite(STDOUT, "Backup created: {$result['name']}" . PHP_EOL);
fwrite(STDOUT, "Size: {$result['size']} bytes" . PHP_EOL);
fwrite(STDOUT, "Stored at: {$result['path']}" . PHP_EOL);
