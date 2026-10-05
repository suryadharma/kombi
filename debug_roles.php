<?php
require_once __DIR__ . '/src/config/database.php';
$db = new Database();
$conn = $db->getConnection();

echo "Distinct Roles:\n";
$stmt = $conn->query("SELECT DISTINCT role FROM assignments");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- '" . $row['role'] . "'\n";
}

echo "\nSample Assignments (first 5):\n";
$stmt = $conn->query("SELECT id, student_id, lecturer_id, role FROM assignments LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Student: {$row['student_id']}, Lecturer: {$row['lecturer_id']}, Role: '{$row['role']}'\n";
}
