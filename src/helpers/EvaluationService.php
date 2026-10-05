<?php

class EvaluationService
{
    public static function getPembimbingSummary(int $studentId, string $stage): array
    {
        $database = new Database();
        $db = $database->getConnection();

        $assignmentQuery = "SELECT role, lecturer_id FROM assignments WHERE student_id = :student_id";
        $assignmentStmt = $db->prepare($assignmentQuery);
        $assignmentStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $assignmentStmt->execute();

        $assignments = [];
        while ($row = $assignmentStmt->fetch(PDO::FETCH_ASSOC)) {
            $assignments[$row['role']] = (int)$row['lecturer_id'];
        }

        $evaluations = self::fetchEvaluations($db, $studentId, $stage, 'dosen_pembimbing');

        $ratio = Settings::getPembimbingRatio();
        $p1Weight = $ratio[0];
        $p2Weight = $ratio[1];

        $pembimbingData = [
            'pembimbing_1' => null,
            'pembimbing_2' => null,
            'aggregate' => null
        ];

        foreach ($evaluations as $evaluation) {
            $rawScore = isset($evaluation['weighted_total']) ? (float)$evaluation['weighted_total'] : ScoreHelper::normalize($evaluation['final_score'] ?? null);
            $normalizedScore = ScoreHelper::normalize($evaluation['final_score'] ?? null);
            $eval = [
                'score' => $rawScore,
                'normalized_score' => $normalizedScore,
                'mode' => $evaluation['mode'],
                'updated_at' => $evaluation['updated_at'],
                'evaluator_name' => $evaluation['evaluator_name']
            ];
            if (isset($assignments['pembimbing_1']) && $assignments['pembimbing_1'] === (int)$evaluation['evaluator_id']) {
                $pembimbingData['pembimbing_1'] = $eval;
            } elseif (isset($assignments['pembimbing_2']) && $assignments['pembimbing_2'] === (int)$evaluation['evaluator_id']) {
                $pembimbingData['pembimbing_2'] = $eval;
            }
        }

        if ($pembimbingData['pembimbing_1'] || $pembimbingData['pembimbing_2']) {
            $rawScores = [];
            $normalizedScores = [];
            if ($pembimbingData['pembimbing_1']) {
                $rawScores[] = $pembimbingData['pembimbing_1']['score'] * $p1Weight;
                if (isset($pembimbingData['pembimbing_1']['normalized_score'])) {
                    $normalizedScores[] = $pembimbingData['pembimbing_1']['normalized_score'] * $p1Weight;
                }
            }
            if ($pembimbingData['pembimbing_2']) {
                $rawScores[] = $pembimbingData['pembimbing_2']['score'] * $p2Weight;
                if (isset($pembimbingData['pembimbing_2']['normalized_score'])) {
                    $normalizedScores[] = $pembimbingData['pembimbing_2']['normalized_score'] * $p2Weight;
                }
            }
            if (!empty($rawScores)) {
                $pembimbingData['aggregate'] = round(array_sum($rawScores), 2);
            }
            if (!empty($normalizedScores)) {
                $pembimbingData['aggregate_normalized'] = round(array_sum($normalizedScores), 2);
            }
        }

        return $pembimbingData;
    }

    public static function getPengujiSummary(int $studentId, string $stage): array
    {
        $database = new Database();
        $db = $database->getConnection();

        $evaluations = self::fetchEvaluations($db, $studentId, $stage, 'dosen_penguji');
        if (empty($evaluations)) {
            return [
                'evaluations' => [],
                'aggregate' => null,
                'aggregate_normalized' => null
            ];
        }

        $rawScores = [];
        $normalizedScores = [];
        foreach ($evaluations as $evaluation) {
            $rawScores[] = isset($evaluation['weighted_total']) ? (float)$evaluation['weighted_total'] : ScoreHelper::normalize($evaluation['final_score'] ?? null);
            $normalizedScores[] = ScoreHelper::normalize($evaluation['final_score'] ?? null);
        }

        $aggregate = null;
        $method = Settings::getPengujiAggregationMethod();
        if ($method === 'median') {
            $filtered = array_values(array_filter($rawScores, static fn($value) => $value !== null));
            sort($filtered);
            $count = count($filtered);
            $middle = (int) floor(($count - 1) / 2);
            if ($count % 2) {
                $aggregate = $filtered[$middle] ?? null;
            } else {
                $aggregate = ($filtered[$middle] + $filtered[$middle + 1]) / 2;
            }
        } else {
            $valid = array_values(array_filter($rawScores, static fn($value) => $value !== null));
            $aggregate = !empty($valid) ? array_sum($valid) / count($valid) : null;
        }

        $aggregateNormalized = null;
        $normalizedFiltered = array_values(array_filter($normalizedScores, static fn($value) => $value !== null));
        if ($method === 'median') {
            sort($normalizedFiltered);
            $count = count($normalizedFiltered);
            $middle = (int) floor(($count - 1) / 2);
            if ($count % 2) {
                $aggregateNormalized = $normalizedFiltered[$middle] ?? null;
            } else {
                $aggregateNormalized = ($normalizedFiltered[$middle] + $normalizedFiltered[$middle + 1]) / 2;
            }
        } else {
            $aggregateNormalized = !empty($normalizedFiltered) ? array_sum($normalizedFiltered) / count($normalizedFiltered) : null;
        }

        return [
            'evaluations' => $evaluations,
            'aggregate' => $aggregate !== null ? round($aggregate, 2) : null,
            'aggregate_normalized' => $aggregateNormalized !== null ? round($aggregateNormalized, 2) : null
        ];
    }

    private static function fetchEvaluations(PDO $db, int $studentId, string $stage, string $role): array
    {
        $query = "SELECT e.*, u.name AS evaluator_name,
                         (
                            SELECT ROUND(SUM(es.score *
                                CASE WHEN ec.weight > 1 THEN ec.weight / 100 ELSE ec.weight END
                            ), 2)
                            FROM evaluation_scores es
                            JOIN evaluation_components ec ON ec.id = es.component_id
                            WHERE es.evaluation_id = e.id
                         ) AS weighted_total
                  FROM evaluations e
                  JOIN users u ON e.evaluator_id = u.id
                  WHERE e.student_id = :student_id
                    AND e.stage = :stage
                    AND e.evaluator_role = :role
                  ORDER BY e.updated_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':stage', $stage);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
