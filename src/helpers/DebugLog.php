<?php

class DebugLog
{
    public static function log(string $message): void
    {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Create table if not exists
            $db->exec("CREATE TABLE IF NOT EXISTS debug_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                message TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            $stmt = $db->prepare("INSERT INTO debug_logs (message) VALUES (?)");
            $stmt->execute([$message]);
        } catch (Exception $e) {
            // Ignore
        }
    }
    
    public static function getLogs(int $limit = 50): array
    {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->query("SELECT * FROM debug_logs ORDER BY id DESC LIMIT $limit");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public static function clear(): void
    {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $db->exec("DELETE FROM debug_logs");
        } catch (Exception $e) {
            // Ignore
        }
    }
}
