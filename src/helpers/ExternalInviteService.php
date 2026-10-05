<?php

class ExternalInviteService
{
    /**
     * Ensure external examiner user and lecturer records exist.
     */
    public static function ensureExternalUser(PDO $db, string $nip, string $name): int
    {
        $select = "SELECT id FROM users WHERE username = :username AND role = 'penguji_eksternal' LIMIT 1";
        $stmt = $db->prepare($select);
        $stmt->bindParam(':username', $nip);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            return (int)$user['id'];
        }

        $password = password_hash($nip, PASSWORD_DEFAULT);
        $insert = "INSERT INTO users (username, password, name, role, nip)
                   VALUES (:username, :password, :name, 'penguji_eksternal', :nip)";
        $insertStmt = $db->prepare($insert);
        $insertStmt->bindParam(':username', $nip);
        $insertStmt->bindParam(':password', $password);
        $insertStmt->bindParam(':name', $name);
        $insertStmt->bindParam(':nip', $nip);
        $insertStmt->execute();
        $userId = (int)$db->lastInsertId();

        $lecturerSelect = "SELECT id FROM lecturers WHERE nip = :nip LIMIT 1";
        $lecturerStmt = $db->prepare($lecturerSelect);
        $lecturerStmt->bindParam(':nip', $nip);
        $lecturerStmt->execute();
        if (!$lecturerStmt->fetch(PDO::FETCH_ASSOC)) {
            $lecturerInsert = "INSERT INTO lecturers (user_id, nip, name, is_external)
                               VALUES (:user_id, :nip, :name, 1)";
            $lecturerInsertStmt = $db->prepare($lecturerInsert);
            $lecturerInsertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $lecturerInsertStmt->bindParam(':nip', $nip);
            $lecturerInsertStmt->bindParam(':name', $name);
            $lecturerInsertStmt->execute();
        }

        return $userId;
    }

    /**
     * Create a new external token for the given event.
     *
     * @return array{token:string,expires_at:string}
     */
    public static function createExternalToken(
        PDO $db,
        int $eventId,
        string $nip,
        string $name,
        string $scheduledDate,
        string $scheduledTime
    ): array {
        $userId = self::ensureExternalUser($db, $nip, $name);

        $deleteExisting = $db->prepare("DELETE FROM external_tokens WHERE event_id = :event_id");
        $deleteExisting->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $deleteExisting->execute();

        $expiresAt = date('Y-m-d H:i:s', strtotime($scheduledDate . ' ' . $scheduledTime) + (5 * 86400));
        $token = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));

        $insertToken = "INSERT INTO external_tokens (nip, token, event_id, expires_at) 
                        VALUES (:nip, :token, :event_id, :expires_at)";
        $tokenStmt = $db->prepare($insertToken);
        $tokenStmt->bindParam(':nip', $nip);
        $tokenStmt->bindParam(':token', $token);
        $tokenStmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $tokenStmt->bindParam(':expires_at', $expiresAt);
        $tokenStmt->execute();

        return ['token' => $token, 'expires_at' => $expiresAt, 'user_id' => $userId];
    }
}
