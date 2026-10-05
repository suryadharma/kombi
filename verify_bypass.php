<?php
require 'src/config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Check the bypass evaluation we created
$stmt = $conn->prepare("SELECT e.id, e.student_id, e.stage, e.mode, e.final_score, e.evaluator_id, e.evaluator_role,
                             s.nim, s.name AS student_name, s.status
                      FROM evaluations e
                      JOIN students s ON e.student_id = s.id
                      WHERE e.mode = 'bypass' AND e.student_id = 333");
$stmt->execute();

echo "Bypass evaluations for student ID 333:\n";
echo "========================================\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . "\n";
    echo "Student ID: " . $row['student_id'] . "\n";
    echo "Student: " . $row['nim'] . " - " . $row['student_name'] . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Stage: " . $row['stage'] . "\n";
    echo "Mode: " . $row['mode'] . "\n";
    echo "Final Score: " . ($row['final_score'] ?? 'NULL') . "\n";
    echo "Evaluator ID: " . $row['evaluator_id'] . "\n";
    echo "Evaluator Role: " . $row['evaluator_role'] . "\n";
    echo "----------------------------------------\n";
}

// Now check if the graduated report query will find this evaluation
echo "\n\nChecking if graduated report query will find this evaluation:\n";
echo "Query: SELECT e.id AS evaluation_id, e.student_id, e.stage, e.mode, e.final_score,\n";
echo "       e.evaluator_role, e.evaluator_id, u.name AS evaluator_name, a.role AS assignment_role\n";
echo "FROM evaluations e\n";
echo "JOIN users u ON e.evaluator_id = u.id\n";
echo "LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id\n";
echo "WHERE e.student_id IN (...) AND (e.final_score IS NOT NULL OR e.mode = 'bypass')\n";

$stmt2 = $conn->prepare("SELECT e.id AS evaluation_id, e.student_id, e.stage, e.mode, e.final_score,
                                 e.evaluator_role, e.evaluator_id, u.name AS evaluator_name, a.role AS assignment_role
                          FROM evaluations e
                          JOIN users u ON e.evaluator_id = u.id
                          LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id
                          WHERE e.student_id = 333 AND (e.final_score IS NOT NULL OR e.mode = 'bypass')
                          ORDER BY e.student_id, e.stage, e.evaluator_id");
$stmt2->execute();

echo "\nResults from graduated report query:\n";
echo "========================================\n";

$count = 0;
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['evaluation_id'] . "\n";
    echo "Stage: " . $row['stage'] . "\n";
    echo "Mode: " . $row['mode'] . "\n";
    echo "Final Score: " . ($row['final_score'] ?? 'NULL') . "\n";
    echo "Evaluator Role: " . ($row['evaluator_role'] ?? 'NULL') . "\n";
    echo "Assignment Role: " . ($row['assignment_role'] ?? 'NULL') . "\n";
    echo "Evaluator Name: " . ($row['evaluator_name'] ?? 'NULL') . "\n";
    echo "----------------------------------------\n";
    $count++;
}

echo "\nTotal evaluations found: " . $count . "\n";
