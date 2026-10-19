<?php

class ExternalInviteService
{
    /**
     * Ensure external examiner user and lecturer records exist.
     */
    public static function ensureExternalUser(PDO $db, string $nip, string $name, ?string $email = null): int
    {
        $userId = self::ensureExternalUserAccount($db, $nip, $name, $email);

        $lecturerSelect = "SELECT id, user_id FROM lecturers WHERE nip = :nip LIMIT 1";
        $lecturerStmt = $db->prepare($lecturerSelect);
        $lecturerStmt->bindParam(':nip', $nip);
        $lecturerStmt->execute();
        $lecturer = $lecturerStmt->fetch(PDO::FETCH_ASSOC);

        if ($lecturer) {
            // Existing lecturer row: only link the account when it is not linked yet.
            if (empty($lecturer['user_id'])) {
                $lecturerUpdate = "UPDATE lecturers SET user_id = :user_id WHERE id = :id";
                $lecturerUpdateStmt = $db->prepare($lecturerUpdate);
                $lecturerUpdateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $lecturerUpdateStmt->bindParam(':id', $lecturer['id'], PDO::PARAM_INT);
                $lecturerUpdateStmt->execute();
            }
        } else {
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
     * Ensure an external examiner user account exists.
     *
     * Creates or reuses the account for the given NIP. New accounts use the NIP
     * as username and default password, mirroring internal lecturer accounts.
     *
     * External lecturers are treated exactly like internal lecturers: the
     * account carries a normal lecturer role ('dosen_pembimbing') and the actual
     * function (pembimbing vs penguji) is decided later by the assignment.
     * "External" is only an attribute stored on the lecturers table
     * (lecturers.is_external). They can still use the token-based external login
     * in addition to the normal NIP/password login.
     */
    public static function ensureExternalUserAccount(PDO $db, string $nip, string $name, ?string $email = null): int
    {
        $select = "SELECT id, role FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($select);
        $stmt->bindParam(':username', $nip);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = (int) $user['id'];

            // Refresh profile details, but never overwrite protected admin accounts.
            $protectedRoles = ['superadmin', 'kombi'];
            if (!in_array($user['role'] ?? '', $protectedRoles, true)) {
                $setParts = ['name = :name', 'nip = :nip'];
                if ($email !== null) {
                    $setParts[] = 'email = :email';
                }
                $update = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";
                $updateStmt = $db->prepare($update);
                $updateStmt->bindParam(':name', $name);
                $updateStmt->bindParam(':nip', $nip);
                if ($email !== null) {
                    $updateStmt->bindValue(':email', $email);
                }
                $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $updateStmt->execute();
            }

            // Guarantee the account can act as a lecturer (external lecturers are
            // functionally identical to internal ones; the assignment drives the role).
            UserRoleService::ensureUserRole($db, $userId, 'dosen_pembimbing');

            return $userId;
        }

        $password = password_hash($nip, PASSWORD_DEFAULT);
        $insert = "INSERT INTO users (username, password, name, role, nip, email)
                   VALUES (:username, :password, :name, 'dosen_pembimbing', :nip, :email)";
        $insertStmt = $db->prepare($insert);
        $insertStmt->bindParam(':username', $nip);
        $insertStmt->bindParam(':password', $password);
        $insertStmt->bindParam(':name', $name);
        $insertStmt->bindParam(':nip', $nip);
        $insertStmt->bindValue(':email', $email);
        $insertStmt->execute();
        $userId = (int) $db->lastInsertId();

        UserRoleService::ensureUserRole($db, $userId, 'dosen_pembimbing');

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
