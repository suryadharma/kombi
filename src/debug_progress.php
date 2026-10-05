<?php
// Standalone debug script
$dbConfig = require __DIR__ . '/config/database.php';

try {
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

function toEventType($stage)
{
    $map = [
        'sempro' => 'seminar_proposal',
        'semhas' => 'seminar_hasil',
        'ujian' => 'sidang_skripsi' // Adjust based on your DB schema if needed, guessing common names
    ];
    // Check what is actually in DB.
    // Based on previous code: EventService::toEventType($stage)
    // Let's just guess or query distinct types from events to be sure.
    // Or just look at the code I read earlier? 
    // I don't have EventService code viewing history nearby to be 100% sure of mapping, 
    // but Controller used it.

    // Let's try to infer from existing events for this student if possible.
    return $map[$stage] ?? $stage;
}

// Let's get the mapping from EventService file if we could, but let's just dump raw events to see types
$nim = '221710301002';
echo "Checking NIM: $nim\n";

$stmt = $db->prepare("SELECT id FROM students WHERE nim = ?");
$stmt->execute([$nim]);
$studentId = $stmt->fetchColumn();

if (!$studentId) {
    die("Student not found\n");
}
echo "Student ID: $studentId\n";

// Dump Events
echo "\n--- RAW EVENTS ---\n";
$stmt = $db->prepare("SELECT * FROM events WHERE student_id = ?");
$stmt->execute([$studentId]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($events as $e) {
    echo "ID: {$e['id']}, Type: {$e['type']}, Status: {$e['status']}\n";
}

$stages = ['sempro'];
// We focus on sempro for now as user complained about it.

foreach ($stages as $stage) {
    echo "\n--- Checking Stage: $stage ---\n";

    // 2. Check Assignments
    $assignStmt = $db->prepare("SELECT id, role, lecturer_id FROM assignments WHERE student_id = ? AND (role LIKE 'pembimbing%' OR role LIKE 'penguji%')");
    $assignStmt->execute([$studentId]);
    $assignments = $assignStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Assignments Count: " . count($assignments) . "\n";
    $assignedLecturers = [];
    foreach ($assignments as $a) {
        echo " - Role: {$a['role']}, Lecturer ID: {$a['lecturer_id']}\n";
        $assignedLecturers[] = $a['lecturer_id'];
    }

    // 3. Check Grades
    $evalStmt = $db->prepare("
        SELECT id, evaluator_id, final_score 
        FROM evaluations 
        WHERE student_id = ? 
          AND stage = ? 
          AND final_score IS NOT NULL
    ");
    $evalStmt->execute([$studentId, $stage]);
    $evaluations = $evalStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "FInal Scores Count: " . count($evaluations) . "\n";
    $gradedLecturers = [];
    foreach ($evaluations as $e) {
        echo " - Evaluator ID: {$e['evaluator_id']}, Score: {$e['final_score']}\n";
        $gradedLecturers[] = $e['evaluator_id'];
    }

    $assignedLecturers = array_unique($assignedLecturers);
    $gradedLecturers = array_unique($gradedLecturers);

    $missing = array_diff($assignedLecturers, $gradedLecturers);

    echo "Unique Assigned: " . count($assignedLecturers) . "\n";
    echo "Unique Graded: " . count($gradedLecturers) . "\n";

    if (count($missing) > 0) {
        echo "Missing grades from Lecturer IDs: " . implode(', ', $missing) . "\n";
    } else {
        echo "All assigned lecturers have graded.\n";
    }
}
