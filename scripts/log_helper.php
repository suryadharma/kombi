<?php
function debug_log($message) {
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }
    $line = '[' . date('c') . '] ' . $message . PHP_EOL;
    file_put_contents($logDir . '/backup_debug.log', $line, FILE_APPEND);
}
