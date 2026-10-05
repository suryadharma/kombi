<?php
require __DIR__ . '/src/config/database.php';
require __DIR__ . '/src/helpers/ScoreHelper.php';

$db = new Database();
$conn = $db->getConnection();

// Get all bypass evaluations
$stmt = $conn->query("
    SELECT e.id, e.student_id, e.stage, e.mode, e.final_score, e.evaluator_id,
           s.nim, s.name as student_name,
           u.name as evaluator_name
    FROM evaluations e
    JOIN students s ON e.student_id = s.id
    JOIN users u ON e.evaluator_id = u.id
    WHERE e.mode = 'bypass' AND s.status = 'LULUS'
    ORDER BY e.student_id
");
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Bypass Evaluations for Graduated Students:\n";
echo "==========================================\n\n";

foreach ($evaluations as $eval) {
    echo "Student: {$eval['nim']} - {$eval['student_name']}\n";
    echo "Evaluator: {$eval['evaluator_name']}\n";
    echo "Stage: {$eval['stage']}\n";
    echo "Final Score in DB: " . ($eval['final_score'] ?? 'NULL') . "\n";

    // Get components for this evaluation
    $stmt2 = $conn->prepare("
        SELECT ec.name, ec.weight, es.score
        FROM evaluation_scores es
        JOIN evaluation_components ec ON ec.id = es.component_id
        WHERE es.evaluation_id = ?
        ORDER BY ec.sort_order, ec.id
    ");
    $stmt2->execute([$eval['id']]);
    $components = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($components)) {
        echo "Components:\n";
        $totalWeighted = 0;
        foreach ($components as $comp) {
            $weight = $comp['weight'] ?? 0;
            $score = $comp['score'] ?? 0;
            $weighted = $score * $weight;
            $totalWeighted += $weighted;
            echo "  - {$comp['name']}: {$score} (weight: {$weight}) = {$weighted}\n";
        }
        echo "Calculated Raw Score: {$totalWeighted}\n";
        $normalized = ScoreHelper::normalize($totalWeighted);
        echo "Normalized Score: {$normalized}\n";
    } else {
        echo "No components found\n";
    }

    echo "\n" . str_repeat("-", 50) . "\n\n";
}
