#!/usr/bin/env php
<?php
/**
 * Trigger Email Processor
 * 
 * This script processes pending emails from the queue.
 * Designed to be run in background via exec().
 * 
 * Usage: php src/scripts/trigger_email.php
 */

// Change to project root directory
chdir(dirname(__DIR__) . '/..');

// Silence output for background execution
ini_set('display_errors', 0);
error_reporting(0);

// Load required files
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../services/EmailService.php';

try {
    $emailService = new EmailService();
    
    // Check if email is enabled
    if (!$emailService->isEnabled()) {
        exit(0);
    }
    
    // Process up to 5 emails at a time (small batch for quick response)
    $result = $emailService->processQueue(5);
    
    // Log result silently
    $logFile = __DIR__ . '/../storage/logs/email_trigger.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    @file_put_contents(
        $logFile, 
        date('Y-m-d H:i:s') . " - Processed: {$result['processed']}, Sent: {$result['sent']}, Failed: {$result['failed']}\n", 
        FILE_APPEND | LOCK_EX
    );
    
} catch (Exception $e) {
    // Silently fail
    @file_put_contents(
        __DIR__ . '/../storage/logs/email_trigger.log', 
        date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", 
        FILE_APPEND | LOCK_EX
    );
}

exit(0);
