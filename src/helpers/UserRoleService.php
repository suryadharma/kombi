<?php

class UserRoleService {
    /**
     * Fetch all roles attached to a user.
     */
    public static function getUserRoles(PDO $db, int $userId): array {
        try {
            $query = "SELECT role FROM user_roles WHERE user_id = :user_id ORDER BY role";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Ensure the given role exists for the user.
     */
    public static function ensureUserRole(PDO $db, int $userId, string $role): void {
        try {
            $check = "SELECT 1 FROM user_roles WHERE user_id = :user_id AND role = :role LIMIT 1";
            $stmt = $db->prepare($check);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetchColumn()) {
                return;
            }

            $insert = "INSERT INTO user_roles (user_id, role) VALUES (:user_id, :role)";
            $stmt = $db->prepare($insert);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            // Table might not exist yet; safely ignore so legacy installations continue to work.
        }
    }

    /**
     * Remove a role from a user.
     */
    public static function removeUserRole(PDO $db, int $userId, string $role): void {
        try {
            $delete = "DELETE FROM user_roles WHERE user_id = :user_id AND role = :role";
            $stmt = $db->prepare($delete);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            // Same reason as above; ignore.
        }
    }
}
