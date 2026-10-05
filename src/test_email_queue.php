<?php
// Simple test script to check email queue
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Load required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/Settings.php';
require_once __DIR__ . '/helpers/AppSettings.php';
require_once __DIR__ . '/services/EmailService.php';

echo "<h2>Email Queue Test</h2>";

$db = new Database();
$conn = $db->getConnection();

// Check email settings
echo "<h3>📧 Email Settings:</h3>";
$enabled = Settings::get('email_enabled', '0');
echo "<p><strong>email_enabled:</strong> " . var_export($enabled, true) . "</p>";

if ($enabled !== '1') {
    echo "<p style='color:orange; font-weight:bold'>⚠️ EMAIL FEATURE IS DISABLED!</p>";
    echo "<p>Email tidak akan di-queue. Untuk mengaktifkan, jalankan SQL berikut:</p>";
    echo "<pre style='background:#f0f0f0; padding:10px; border:1px solid #ccc'>";
    echo "INSERT INTO settings (key_name, value, description)
VALUES ('email_enabled', '1', 'Aktifkan fitur pengiriman email otomatis ke dosen')
ON DUPLICATE KEY UPDATE value = '1';";
    echo "</pre>";
    echo "<p><a href='?enable=1'>Click here to enable email feature</a></p>";
    echo "<hr>";
}

// Handle enable request
if (isset($_GET['enable']) && $_GET['enable'] == '1') {
    $stmt = $conn->prepare("INSERT INTO settings (key_name, value, description)
                            VALUES ('email_enabled', '1', 'Aktifkan fitur pengiriman email otomatis ke dosen')
                            ON DUPLICATE KEY UPDATE value = '1'");
    $stmt->execute();
    echo "<p style='color:green; font-weight:bold'>✅ Email feature enabled! <a href=''>Refresh</a></p>";
    echo "<hr>";
}

// Check email_queue table exists
$stmt = $conn->query("SHOW TABLES LIKE 'email_queue'");
if ($stmt->rowCount() === 0) {
    echo "<p style='color:red'>❌ Table 'email_queue' does not exist!</p>";
    echo "<p>Please run the migration: <code>migrations/add_email_feature.sql</code></p>";
    exit;
}

// Check queue contents
$stmt = $conn->query("SELECT COUNT(*) as total FROM email_queue WHERE status = 'PENDING'");
$count = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<p>📧 Pending emails in queue: <strong>{$count['total']}</strong></p>";

// Show recent emails
$stmt = $conn->query("SELECT id, to_email, subject, status, created_at FROM email_queue ORDER BY created_at DESC LIMIT 5");
$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($emails)) {
    echo "<h3>Recent Emails:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>To</th><th>Subject</th><th>Status</th><th>Created</th></tr>";
    foreach ($emails as $email) {
        echo "<tr>";
        echo "<td>{$email['id']}</td>";
        echo "<td>{$email['to_email']}</td>";
        echo "<td>{$email['subject']}</td>";
        echo "<td>{$email['status']}</td>";
        echo "<td>{$email['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No emails in queue.</p>";
}

// Show all emails including failed ones
echo "<h3>All Emails (including FAILED):</h3>";
$stmt = $conn->query("SELECT id, to_email, subject, status, error_message, created_at, sent_at FROM email_queue ORDER BY created_at DESC LIMIT 10");
$allEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($allEmails)) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
    echo "<tr style='background:#f0f0f0'><th>ID</th><th>To</th><th>Subject</th><th>Status</th><th>Error</th><th>Created</th></tr>";
    foreach ($allEmails as $email) {
        $rowColor = '';
        if ($email['status'] === 'SENT') $rowColor = '#d4edda';
        elseif ($email['status'] === 'FAILED') $rowColor = '#f8d7da';
        elseif ($email['status'] === 'PENDING') $rowColor = '#fff3cd';
        
        echo "<tr style='background:{$rowColor}'>";
        echo "<td>{$email['id']}</td>";
        echo "<td>{$email['to_email']}</td>";
        echo "<td>{$email['subject']}</td>";
        echo "<td><strong>{$email['status']}</strong></td>";
        echo "<td>" . ($email['error_message'] ?: '-') . "</td>";
        echo "<td>{$email['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No emails found in queue at all.</p>";
}

// Test processing
echo "<h3>Test Processing:</h3>";
try {
    $emailService = new EmailService($conn);
    
    if (!$emailService->isEnabled()) {
        echo "<p style='color:orange'>⚠️ Email feature is disabled in settings.</p>";
        echo "<p>Enable it at: <strong>/settings</strong> (set 'email_enabled' to '1')</p>";
    } else {
        echo "<p style='color:green'>✅ Email feature is enabled.</p>";
        
        $result = $emailService->processQueue(5);
        echo "<p>Processed: <strong>{$result['processed']}</strong>, Sent: <strong>{$result['sent']}</strong>, Failed: <strong>{$result['failed']}</strong></p>";
        
        if (!empty($result['errors'])) {
            echo "<h4>Errors:</h4>";
            echo "<ul>";
            foreach ($result['errors'] as $error) {
                echo "<li style='color:red'>$error</li>";
            }
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Debug: Check lecturer email
echo "<h3>🔍 Debug: Check Lecturer Email</h3>";
$lecturerId = 14; // Based on logs
$stmt = $conn->prepare("SELECT u.id, u.name, l.email
                       FROM users u
                       LEFT JOIN lecturers l ON u.id = l.user_id
                       WHERE u.id = :lecturer_id");
$stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
$stmt->execute();
$lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

if ($lecturer) {
    echo "<p><strong>Lecturer ID {$lecturerId}:</strong> {$lecturer['name']}</p>";
    echo "<p><strong>Email:</strong> " . ($lecturer['email'] ?: '<span style="color:red">EMPTY / NOT SET</span>') . "</p>";
    
    if (empty($lecturer['email'])) {
        echo "<p style='color:orange'>⚠️ Lecturer tidak punya email! Email tidak bisa dikirim.</p>";
        
        // Handle set email request
        if (isset($_GET['set_email']) && $_GET['set_email'] == '1') {
            $testEmail = 'dosen' . $lecturerId . '@example.com';
            $stmt = $conn->prepare("UPDATE lecturers SET email = :email WHERE user_id = :user_id");
            $stmt->bindParam(':email', $testEmail);
            $stmt->bindParam(':user_id', $lecturerId, PDO::PARAM_INT);
            $stmt->execute();
            echo "<p style='color:green; font-weight:bold'>✅ Email set to: {$testEmail} <a href=''>Refresh</a></p>";
        } else {
            echo "<p><a href='?set_email=1' style='background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px'>📧 Set Email Dosen (Otomatis)</a></p>";
        }
    } else {
        echo "<p style='color:green'>✅ Lecturer sudah punya email!</p>";
    }
} else {
    echo "<p style='color:red'>❌ Lecturer ID {$lecturerId} not found!</p>";
}

echo "<hr>";
echo "<p><a href='/'>Back to Home</a></p>";
