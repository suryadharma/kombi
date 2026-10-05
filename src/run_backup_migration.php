<?php
/**
 * Run migration for backup_settings table
 */

require_once __DIR__ . '/helpers/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read migration SQL
    $sql = file_get_contents(__DIR__ . '/../migrations/add_backup_settings_table.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    
    echo "Starting migration for backup_settings table...\n";
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }
        
        echo "Executing: " . substr($statement, 0, 100) . "...\n";
        
        try {
            $db->exec($statement);
            echo "✓ Success\n";
        } catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nMigration completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
