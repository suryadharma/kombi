<?php

class Assignment {
    private $conn;
    private $table = "assignments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function setAssignment($studentId, $role, $lecturerId, $reason, $assignedBy, $effectiveDate) {
        $query = "INSERT INTO {$this->table} 
                  (student_id, lecturer_id, role, reason, assigned_by, effective_date)
                  VALUES (:student_id, :lecturer_id, :role, :reason, :assigned_by, :effective_date)
                  ON DUPLICATE KEY UPDATE 
                    lecturer_id = VALUES(lecturer_id),
                    reason = VALUES(reason),
                    assigned_by = VALUES(assigned_by),
                    effective_date = VALUES(effective_date),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
        $stmt->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
        $stmt->bindParam(':effective_date', $effectiveDate, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function removeAssignment($studentId, $role) {
        $query = "DELETE FROM {$this->table} WHERE student_id = :student_id AND role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function getAssignmentsByStudent($studentId) {
        $query = "SELECT a.*, u.name AS lecturer_name, u.username AS lecturer_username
                  FROM {$this->table} a
                  JOIN users u ON a.lecturer_id = u.id
                  WHERE a.student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        $assignments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assignments[$row['role']] = $row;
        }

        return $assignments;
    }

    public function getHistory($filters = []) {
        $query = "SELECT a.*, 
                         s.name AS student_name,
                         s.nim AS student_nim,
                         u.name AS lecturer_name,
                         u.username AS lecturer_username,
                         ab.name AS assigned_by_name
                  FROM {$this->table} a
                  JOIN students s ON a.student_id = s.id
                  JOIN users u ON a.lecturer_id = u.id
                  LEFT JOIN users ab ON a.assigned_by = ab.id
                  WHERE 1=1";

        $params = [];

        if (!empty($filters['student_id'])) {
            $query .= " AND a.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND a.effective_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND a.effective_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $query .= " ORDER BY a.effective_date DESC, a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            if ($key === ':student_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
