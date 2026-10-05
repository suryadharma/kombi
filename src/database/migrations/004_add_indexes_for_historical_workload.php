<?php

/**
 * Migration 004: Add indexes for historical workload report performance
 * 
 * This migration adds database indexes to optimize queries for the historical workload report,
 * especially when handling concurrent access by hundreds of users.
 */

// Check if indexes already exist
$checkIndexes = "
    SELECT INDEX_NAME 
    FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('assignments', 'students')
    AND INDEX_NAME IN ('idx_assignments_lecturer', 'idx_students_angkatan', 'idx_students_status_lulus', 'idx_assignments_student')
";

$result = $pdo->query($checkIndexes)->fetchAll();
$existingIndexes = array_column($result, 'INDEX_NAME');

$indexesToCreate = [
    // Assignments table indexes
    'idx_assignments_lecturer' => "CREATE INDEX idx_assignments_lecturer ON assignments(lecturer_id)",
    'idx_assignments_student' => "CREATE INDEX idx_assignments_student ON assignments(student_id)",
    'idx_assignments_role' => "CREATE INDEX idx_assignments_role ON assignments(role)",
    
    // Students table indexes
    'idx_students_angkatan' => "CREATE INDEX idx_students_angkatan ON students(angkatan)",
    'idx_students_status' => "CREATE INDEX idx_students_status ON students(status)",
    'idx_students_semester_lulus' => "CREATE INDEX idx_students_semester_lulus ON students(semester_lulus)",
    'idx_students_composite_status_angkatan' => "CREATE INDEX idx_students_composite_status_angkatan ON students(status, angkatan)",
];

$createdIndexes = [];
$skippedIndexes = [];

foreach ($indexesToCreate as $indexName => $sql) {
    if (!in_array($indexName, $existingIndexes)) {
        try {
            $pdo->exec($sql);
            $createdIndexes[] = $indexName;
        } catch (PDOException $e) {
            // Log error but continue
            error_log("Failed to create index $indexName: " . $e->getMessage());
        }
    } else {
        $skippedIndexes[] = $indexName;
    }
}

echo "Migration 004 completed:\n";
if (!empty($createdIndexes)) {
    echo "Created indexes: " . implode(', ', $createdIndexes) . "\n";
}
if (!empty($skippedIndexes)) {
    echo "Skipped existing indexes: " . implode(', ', $skippedIndexes) . "\n";
}
