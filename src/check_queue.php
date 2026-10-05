<?php
// Quick script to check and process email queue
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/services/EmailService.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== Email Queue Status ===\n\n";

// Check queue
$stmt = $conn->query("SELECT id, recipient_email, subject, status, created_at FROM email_queue ORDER BY created_at DESC LIMIT 10");
$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($emails)) {
    echo "❌ No emails in queue!\n\n";
    
    // Check if email is enabled
    $stmt = $conn->query("SELECT * FROM settings WHERE setting_key = 'email_enabled'");
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Email enabled setting: " . ($setting['setting_value'] ?? 'NOT SET') . "\n";
} else {
    echo "✅ Found " . count($emails) . " email(s) in queue:\n\n";
    foreach ($emails as $email) {
        echo "ID: {$email['id']}\n";
        echo "To: {$email['recipient_email']}\n";
        echo "Subject: {$email['subject']}\n";
        echo "Status: {$email['status']}\n";
        echo "Created: {$email['created_at']}\n";
        echo "---\n";
    }
    
    echo "\n=== Processing Queue ===\n";
    $emailService = new EmailService($conn);
    $result = $emailService->processQueue(5);
    
    echo "\nProcessed: {$result['processed']}\n";
    echo "Sent: {$result['sent']}\n";
    echo "Failed: {$result['failed']}\n";
    
    if (!empty($result['errors'])) {
        echo "\nErrors:\n";
        foreach ($result['errors'] as $error) {
            echo "  - $error\n";
        }
    }
}
