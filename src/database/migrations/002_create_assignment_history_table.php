<?php

class CreateAssignmentHistoryTable {
    public static function up(PDO $db) {
        $sql = "
            CREATE TABLE IF NOT EXISTS assignment_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_id INT NOT NULL,
                lecturer_id INT NOT NULL,
                role ENUM('pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3') NOT NULL,
                reason TEXT NULL,
                assigned_by INT NOT NULL,
                effective_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
                FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_student_role (student_id, role),
                INDEX idx_effective_date (effective_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->exec($sql);
    }
    
    public static function down(PDO $db) {
        $sql = "DROP TABLE IF EXISTS assignment_history";
        $db->exec($sql);
    }
}
