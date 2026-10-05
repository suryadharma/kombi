<?php
require 'src/config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get all graduated students
$stmt = $conn->query("SELECT id, nim, name FROM students WHERE status = 'LULUS'");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "All graduated students:\n";
echo "====================\n";

foreach ($students as $student) {
    echo "ID: " . $student['id'] . ", NIM: " . $student['nim'] . ", Name: " . $student['name'] . "\n";
}

echo "\n\n";

// Check bypass evaluations for each student
echo "Bypass evaluations per student:\n";
echo "========================================\n";

foreach ($students as $student) {
    $stmt2 = $conn->prepare("SELECT e.id, e.stage, e.mode, e.final_score 
                             FROM evaluations e 
                             WHERE e.student_id = ? AND e.mode = 'bypass'");
    $stmt2->execute([$student['id']]);
    
    $hasBypass = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    if ($hasBypass) {
        echo "Student: " . $student['nim'] . " - " . $student['name'] . " HAS bypass evaluation\n";
        echo "  ID: " . $hasBypass['id'] . "\n";
        echo "  Stage: " . $hasBypass['stage'] . "\n";
        echo "  Mode: " . $hasBypass['mode'] . "\n";
        echo "  Final Score: " . $hasBypass['final_score'] . "\n";
    } else {
        echo "Student: " . $student['nim'] . " - " . $student['name'] . " NO bypass evaluation\n";
    }
}
