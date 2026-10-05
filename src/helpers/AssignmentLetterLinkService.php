<?php

class AssignmentLetterLinkService
{
    private static $ensured = false;

    public static function ensureTable(PDO $db): void
    {
        if (self::$ensured) {
            return;
        }
        $sql = "
            CREATE TABLE IF NOT EXISTS assignment_letter_links (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                lecturer_id INT NOT NULL,
                role ENUM('pembimbing_1','pembimbing_2','penguji_1','penguji_2','penguji_3') NOT NULL,
                letter_url TEXT NOT NULL,
                source ENUM('proposal','assignment','manual') DEFAULT 'proposal',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_student_lecturer_role (student_id, lecturer_id, role),
                INDEX idx_student_role (student_id, role),
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $db->exec($sql);
        self::$ensured = true;
    }

    public static function upsert(
        PDO $db,
        int $studentId,
        int $lecturerId,
        string $role,
        string $letterUrl,
        string $source = 'proposal'
    ): void {
        if ($studentId <= 0 || $lecturerId <= 0 || trim($letterUrl) === '') {
            return;
        }
        self::ensureTable($db);
        $query = "
            INSERT INTO assignment_letter_links (student_id, lecturer_id, role, letter_url, source)
            VALUES (:student_id, :lecturer_id, :role, :letter_url, :source)
            ON DUPLICATE KEY UPDATE
                letter_url = VALUES(letter_url),
                source = VALUES(source),
                updated_at = CURRENT_TIMESTAMP
        ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':letter_url', trim($letterUrl));
        $stmt->bindValue(':source', $source);
        $stmt->execute();
    }

    public static function remove(PDO $db, int $studentId, int $lecturerId, string $role): void
    {
        self::ensureTable($db);
        $stmt = $db->prepare("
            DELETE FROM assignment_letter_links
            WHERE student_id = :student_id AND lecturer_id = :lecturer_id AND role = :role
        ");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
    }

    public static function getLinksForStudent(PDO $db, int $studentId): array
    {
        if ($studentId <= 0) {
            return [];
        }
        self::ensureTable($db);
        $stmt = $db->prepare("
            SELECT role, lecturer_id, letter_url
            FROM assignment_letter_links
            WHERE student_id = :student_id
            ORDER BY updated_at DESC
        ");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $role = $row['role'];
            $lecturerId = (int) $row['lecturer_id'];
            if (!isset($map[$role])) {
                $map[$role] = [];
            }
            $map[$role][$lecturerId] = $row['letter_url'];
        }
        return $map;
    }

    public static function getLinksForStudents(PDO $db, array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds))));
        if (empty($studentIds)) {
            return [];
        }
        self::ensureTable($db);
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $db->prepare("
            SELECT student_id, role, lecturer_id, letter_url
            FROM assignment_letter_links
            WHERE student_id IN ($placeholders)
            ORDER BY updated_at DESC
        ");
        foreach ($studentIds as $index => $studentId) {
            $stmt->bindValue($index + 1, $studentId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $studentId = (int) $row['student_id'];
            $role = $row['role'];
            $lecturerId = (int) $row['lecturer_id'];
            if (!isset($map[$studentId])) {
                $map[$studentId] = [];
            }
            if (!isset($map[$studentId][$role])) {
                $map[$studentId][$role] = [];
            }
            if (!isset($map[$studentId][$role][$lecturerId])) {
                $map[$studentId][$role][$lecturerId] = $row['letter_url'];
            }
        }
        return $map;
    }

    public static function getLink(PDO $db, int $studentId, int $lecturerId, string $role): ?string
    {
        if ($studentId <= 0 || $lecturerId <= 0) {
            return null;
        }
        self::ensureTable($db);
        $stmt = $db->prepare("
            SELECT letter_url
            FROM assignment_letter_links
            WHERE student_id = :student_id
              AND lecturer_id = :lecturer_id
              AND role = :role
            LIMIT 1
        ");
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
        $url = $stmt->fetchColumn();
        return $url !== false ? $url : null;
    }
}
