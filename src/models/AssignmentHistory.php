<?php

class AssignmentHistory
{
    private $conn;
    private $table = "assignment_history";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Record a new assignment change
     */
    public function recordChange(PDO $db, int $studentId, string $role, string $newLecturerId, ?string $reason = null): bool
    {
        $sql = "INSERT INTO {$this->table} 
                  (student_id, lecturer_id_old, lecturer_id_new, role, reason, assigned_by, effective_date, created_at)
                  VALUES (:student_id, :lecturer_id_old, :lecturer_id_new, :role, :reason, :assigned_by, :effective_date, CURRENT_TIMESTAMP)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

        // Get old lecturer ID if provided
        if ($newLecturerId !== null) {
            $stmt->bindValue(':lecturer_id_old', $newLecturerId, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':lecturer_id_old', null, PDO::PARAM_NULL);
        }

        $stmt->bindValue(':lecturer_id_new', $newLecturerId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmt->bindParam(':assigned_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindParam(':effective_date', date('Y-m-d'), PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Get history of assignment changes for a student
     */
    public function getHistory(?array $filters = []): array
    {
        $sql = "SELECT ah.id,
                          ah.student_id,
                          s.nim AS student_nim,
                          s.name AS student_name,
                          ah.lecturer_id_old,
                          u_old.name AS lecturer_old_name,
                          ah.lecturer_id_new,
                          u_new.name AS lecturer_new_name,
                          ah.role,
                          ah.reason,
                          ah.assigned_by,
                          u_assigned.name AS assigned_by_name,
                          ah.effective_date,
                          ah.created_at
                   FROM {$this->table} ah
                   JOIN students s ON ah.student_id = s.id
                   LEFT JOIN users u_old ON ah.lecturer_id_old = u_old.id
                   LEFT JOIN users u_new ON ah.lecturer_id_new = u_new.id
                   LEFT JOIN users u_assigned ON ah.assigned_by = u_assigned.id
                   WHERE 1=1";

        $params = [];

        if (!empty($filters['student_id'])) {
            $sql .= " AND ah.student_id = :student_id";
            $params[':student_id'] = (int) $filters['student_id'];
        }

        if (!empty($filters['role'])) {
            $sql .= " AND ah.role = :role";
            $params[':role'] = $filters['role'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND ah.effective_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND ah.effective_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY ah.effective_date DESC, ah.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get current assignment for a student and role
     */
    public function getCurrentAssignment(PDO $db, int $studentId, string $role): ?array
    {
        $sql = "SELECT a.lecturer_id, u.name 
                   FROM assignments a
                   JOIN users u ON a.lecturer_id = u.id
                   WHERE a.student_id = :student_id AND a.role LIKE :role
                   ORDER BY a.created_at DESC
                   LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':role', $role . '%', PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
