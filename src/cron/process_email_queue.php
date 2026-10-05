#!/usr/bin/env php
<?php
/**
 * Email Queue Processor
 * 
 * This script processes the email queue and sends emails asynchronously.
 * Should be run via cron job every 5-10 minutes.
 * 
 * Usage: php src/cron/process_email_queue.php
 * 
 * Cron setup:
 * *\/5 * * * * cd /path/to/kombi && php src/cron/process_email_queue.php >> logs/email_queue.log 2>&1
 */

// Change to project root directory
chdir(dirname(__DIR__) . '/..');

// Load required files
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../services/EmailService.php';

// Prevent concurrent execution
$lockFile = __DIR__ . '/email_queue.lock';
$lockHandle = fopen($lockFile, 'w');

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Another instance is already running. Exiting.\n";
    exit(0);
}

echo "[" . date('Y-m-d H:i:s') . "] Email Queue Processor Started\n";
echo "=====================================\n";

try {
    $emailService = new EmailService();
    
    // Check if email is enabled
    if (!$emailService->isEnabled()) {
        echo "[INFO] Email feature is disabled. Exiting.\n";
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        exit(0);
    }
    
    // Get batch size from settings
    $batchSize = (int) Settings::get('email_batch_size', '10');
    
    echo "[INFO] Processing up to {$batchSize} emails...\n";
    
    // Process the queue
    $result = $emailService->processQueue($batchSize);
    
    echo "=====================================\n";
    echo "[SUMMARY]\n";
    echo "Processed: {$result['processed']}\n";
    echo "Sent:      {$result['sent']}\n";
    echo "Failed:    {$result['failed']}\n";
    
    if (!empty($result['errors'])) {
        echo "\n[ERRORS]\n";
        foreach ($result['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }
    
    echo "\n[QUEUE STATS]\n";
    $stats = $emailService->getQueueStats();
    echo "Pending:   {$stats['pending']}\n";
    echo "Processing: {$stats['processing']}\n";
    echo "Sent:      {$stats['sent']}\n";
    echo "Failed:    {$stats['failed']}\n";
    
    // Clean up old sent/failed emails
    $maxAge = (int) Settings::get('email_max_queue_age', '24');
    $db = new Database();
    $conn = $db->getConnection();
    
    $cleanupQuery = "DELETE FROM email_queue 
        WHERE status IN ('SENT', 'FAILED') 
        AND sent_at < DATE_SUB(NOW(), INTERVAL :max_age HOUR)";
    $stmt = $conn->prepare($cleanupQuery);
    $stmt->bindValue(':max_age', $maxAge, PDO::PARAM_INT);
    $stmt->execute();
    $cleaned = $stmt->rowCount();
    
    if ($cleaned > 0) {
        echo "\n[CLEANUP] Removed {$cleaned} old sent/failed emails from queue\n";
    }
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Email Queue Processor Completed\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo "[TRACE]\n" . $e->getTraceAsString() . "\n";
    error_log("Email Queue Processor Error: " . $e->getMessage());
}

// Release lock
flock($lockHandle, LOCK_UN);
fclose($lockHandle);

exit(0);
