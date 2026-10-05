<?php
require_once __DIR__ . '/helpers/Settings.php';
require_once __DIR__ . '/helpers/AppSettings.php';
require_once __DIR__ . '/config/Database.php';

use database\Database;

// Test Settings::get()
echo "=== Testing Settings::get() ===\n";
$footerText = Settings::get('app_footer_text', 'DEFAULT_FOOTER');
$devName = Settings::get('app_developer_name', 'DEFAULT_DEV');
$devUrl = Settings::get('app_developer_url', 'DEFAULT_URL');

echo "app_footer_text: " . var_export($footerText, true) . "\n";
echo "app_developer_name: " . var_export($devName, true) . "\n";
echo "app_developer_url: " . var_export($devUrl, true) . "\n";

// Test AppSettings methods
echo "\n=== Testing AppSettings methods ===\n";
echo "getFooterText(): " . var_export(AppSettings::getFooterText(), true) . "\n";
echo "getDeveloperName(): " . var_export(AppSettings::getDeveloperName(), true) . "\n";
echo "getDeveloperUrl(): " . var_export(AppSettings::getDeveloperUrl(), true) . "\n";
echo "getDeveloperLink(): " . var_export(AppSettings::getDeveloperLink(), true) . "\n";
echo "getCopyrightText(): " . var_export(AppSettings::getCopyrightText(), true) . "\n";

// Test direct database query
echo "\n=== Testing direct database query ===\n";
try {
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT key_name, value FROM settings WHERE key_name IN ('app_footer_text', 'app_developer_name', 'app_developer_url')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Database results:\n";
    foreach ($results as $row) {
        echo "  {$row['key_name']}: {$row['value']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
