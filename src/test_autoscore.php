<?php
// Test script to check auto-score logic
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/ScoreHelper.php';

$database = new Database();
$db = $database->getConnection();

$studentId = 1216;
$stage = 'ujian';

// Get pembimbing scores
$semproQuery = "SELECT e.final_score FROM evaluations e 
               WHERE e.student_id = :student_id AND e.stage = 'sempro' AND e.evaluator_role = 'dosen_pembimbing'";
$semproStmt = $db->prepare($semproQuery);
$semproStmt->bindParam(':student_id', $studentId);
$semproStmt->execute();

$scores = [];
if ($semproStmt->rowCount() > 0) {
    $semproScore = $semproStmt->fetch(PDO::FETCH_ASSOC);
    $scores['sempro'] = ScoreHelper::normalize($semproScore['final_score'] ?? null);
}

$semhasQuery = "SELECT e.final_score FROM evaluations e 
               WHERE e.student_id = :student_id AND e.stage = 'semhas' AND e.evaluator_role = 'dosen_pembimbing'";
$semhasStmt = $db->prepare($semhasQuery);
$semhasStmt->bindParam(':student_id', $studentId);
$semhasStmt->execute();

if ($semhasStmt->rowCount() > 0) {
    $semhasScore = $semhasStmt->fetch(PDO::FETCH_ASSOC);
    $scores['semhas'] = ScoreHelper::normalize($semhasScore['final_score'] ?? null);
}

echo "Pembimbing Scores: " . json_encode($scores) . "\n";

// Get components
$componentsQuery = "SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY sort_order";
$componentsStmt = $db->prepare($componentsQuery);
$componentsStmt->bindParam(':stage', $stage);
$componentsStmt->execute();
$components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nComponents:\n";
foreach ($components as $component) {
    $autoScore = null;
    if (strpos($component['name'], 'Seminar Proposal') !== false && isset($scores['sempro'])) {
        $autoScore = $scores['sempro'];
        echo "  - {$component['name']}: MATCH (Seminar Proposal) -> auto_score = {$autoScore}\n";
    } elseif (strpos($component['name'], 'Seminar Hasil') !== false && isset($scores['semhas'])) {
        $autoScore = $scores['semhas'];
        echo "  - {$component['name']}: MATCH (Seminar Hasil) -> auto_score = {$autoScore}\n";
    } else {
        echo "  - {$component['name']}: NO MATCH\n";
    }
}
