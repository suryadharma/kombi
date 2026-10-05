<?php
require 'src/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT e.id, e.student_id, s.nim, s.name, s.status, e.stage, e.mode, e.final_score 
                      FROM evaluations e 
                      JOIN students s ON e.student_id = s.id 
                      WHERE s.status = 'LULUS' AND e.mode = 'bypass'");

echo "Bypass evaluations for graduated students:\n";
echo "========================================\n";

$count = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . "\n";
    echo "Student ID: " . $row['student_id'] . "\n";
    echo "NIM: " . $row['nim'] . "\n";
    echo "Name: " . $row['name'] . "\n";
    echo "Status: " . $row['status'] . "\n";
    echo "Stage: " . $row['stage'] . "\n";
    echo "Mode: " . $row['mode'] . "\n";
    echo "Final Score: " . ($row['final_score'] ?? 'NULL') . "\n";
    echo "----------------------------------------\n";
    $count++;
}

echo "\nTotal bypass evaluations: " . $count . "\n";

// Also check all evaluations for graduated students
echo "\n\nAll evaluations for graduated students:\n";
echo "========================================\n";

$stmt2 = $conn->query("SELECT e.id, e.student_id, s.nim, s.name, s.status, e.stage, e.mode, e.final_score 
                       FROM evaluations e 
                       JOIN students s ON e.student_id = s.id 
                       WHERE s.status = 'LULUS'");

$count2 = 0;
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . ", Student: " . $row['nim'] . " - " . $row['name'] . ", Stage: " . $row['stage'] . ", Mode: " . $row['mode'] . ", Final Score: " . ($row['final_score'] ?? 'NULL') . "\n";
    $count2++;
}

echo "\nTotal evaluations for graduated students: " . $count2 . "\n";
