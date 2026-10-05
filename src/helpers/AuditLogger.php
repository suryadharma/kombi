<?php

class AuditLogger
{
    /**
     * Persist an audit log entry.
     */
    public static function log(int $userId, string $action, string $targetType, int $targetId, string $description): void
    {
        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "INSERT INTO audit_logs (user_id, action, target_type, target_id, description)
                      VALUES (:user_id, :action, :target_type, :target_id, :description)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':target_type', $targetType);
            $stmt->bindParam(':target_id', $targetId, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
        } catch (Exception $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}
