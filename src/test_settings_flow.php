<?php
/**
 * Test script to verify email settings save/load flow
 * Run this script to test if settings are being saved and loaded correctly
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/Settings.php';

echo "=== Email Settings Test ===\n\n";

// Test 1: Check current values in database
echo "1. Current values in database:\n";
echo "-----------------------------------\n";
$keys = ['email_enabled', 'email_mode', 'email_username', 'email_password', 'email_port', 'email_encryption'];
foreach ($keys as $key) {
    $value = Settings::get($key, 'NOT_FOUND');
    echo "  $key = '$value'\n";
}

echo "\n2. Testing Settings::get() method:\n";
echo "-----------------------------------\n";
echo "  email_gmail (via email_username): " . Settings::get('email_username', 'DEFAULT') . "\n";
echo "  app_password (via email_password): " . Settings::get('email_password', 'DEFAULT') . "\n";
echo "  port: " . Settings::get('email_port', 'DEFAULT') . "\n";
echo "  encryption: " . Settings::get('email_encryption', 'DEFAULT') . "\n";
echo "  enabled: " . Settings::get('email_enabled', 'DEFAULT') . "\n";
echo "  email_mode: " . Settings::get('email_mode', 'DEFAULT') . "\n";

echo "\n3. Direct database query:\n";
echo "-----------------------------------\n";
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT key_name, value FROM settings WHERE key_name LIKE 'email_%' ORDER BY key_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $value = $row['value'] === '' ? '(empty string)' : $row['value'];
        echo "  {$row['key_name']} = '$value'\n";
    }
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Simulating form submission data:\n";
echo "-----------------------------------\n";
echo "  Form field: email[email_gmail] -> Should save to: email_username\n";
echo "  Form field: email[app_password] -> Should save to: email_password\n";
echo "  Form field: email[port] -> Should save to: email_port\n";
echo "  Form field: email[encryption] -> Should save to: email_encryption\n";
echo "  Form field: email[enabled] -> Should save to: email_enabled\n";

echo "\n5. Cache status:\n";
echo "-----------------------------------\n";
echo "  Cache is a static array in Settings class\n";
echo "  Cache is cleared after each save operation\n";
echo "  Cache should be automatically cleared between requests\n";

echo "\n=== Test Complete ===\n";
echo "\nIf you're experiencing settings loss after restart:\n";
echo "- Check if the database container is running: docker ps\n";
echo "- Check if the database volume is persisted: docker volume ls\n";
echo "- Check browser console for JavaScript errors when saving\n";
echo "- Check PHP error logs for database errors\n";
