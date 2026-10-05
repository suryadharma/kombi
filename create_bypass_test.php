<?php
require 'src/config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get student ID for graduated student
$stmt = $conn->query("SELECT id, nim, name FROM students WHERE status = 'LULUS' LIMIT 1");
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "No graduated student found!\n";
    exit(1);
}

echo "Creating bypass evaluation for student: " . $student['nim'] . " - " . $student['name'] . " (ID: " . $student['id'] . ")\n";

// Get an evaluator
$stmt2 = $conn->query("SELECT id, name FROM users WHERE role IN ('kombi', 'superadmin') LIMIT 1");
$evaluator = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$evaluator) {
    echo "No evaluator found!\n";
    exit(1);
}

echo "Using evaluator: " . $evaluator['name'] . " (ID: " . $evaluator['id'] . ")\n";

// Insert bypass evaluation for ujian stage
$sql = "INSERT INTO evaluations (student_id, stage, mode, evaluator_id, evaluator_role, final_score, notes, created_at, updated_at) 
        VALUES (:student_id, 'ujian', 'bypass', :evaluator_id, 'kombi', 85.50, 'Test bypass evaluation untuk mahasiswa lulus', NOW(), NOW())";

$stmt3 = $conn->prepare($sql);
$stmt3->execute([
    ':student_id' => $student['id'],
    ':evaluator_id' => $evaluator['id']
]);

$evalId = $conn->lastInsertId();
echo "Created bypass evaluation ID: " . $evalId . "\n";
echo "Final Score: 85.50\n";

// Verify the evaluation was created
$stmt4 = $conn->prepare("SELECT * FROM evaluations WHERE id = :id");
$stmt4->execute([':id' => $evalId]);
$result = $stmt4->fetch(PDO::FETCH_ASSOC);

echo "\nVerification:\n";
echo "ID: " . $result['id'] . "\n";
echo "Student ID: " . $result['student_id'] . "\n";
echo "Stage: " . $result['stage'] . "\n";
echo "Mode: " . $result['mode'] . "\n";
echo "Evaluator ID: " . $result['evaluator_id'] . "\n";
echo "Evaluator Role: " . $result['evaluator_role'] . "\n";
echo "Final Score: " . $result['final_score'] . "\n";
echo "Notes: " . $result['notes'] . "\n";

echo "\nTest bypass evaluation created successfully!\n";
echo "Now check the graduated report page at: http://localhost:9201/reports/graduated\n";
