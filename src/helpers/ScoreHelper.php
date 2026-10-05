<?php

class ScoreHelper
{
    public static function weightedTotalFromComponents(array $components): ?float
    {
        if (empty($components)) {
            return null;
        }

        $total = 0.0;
        $hasScore = false;
        foreach ($components as $component) {
            if (!isset($component['score'])) {
                continue;
            }
            $scoreValue = (float) $component['score'];
            $weightRaw = isset($component['weight']) ? (float) $component['weight'] : 0.0;
            $weightFraction = $weightRaw > 1 ? $weightRaw / 100 : $weightRaw;
            if ($weightFraction <= 0) {
                continue;
            }
            $total += $scoreValue * $weightFraction;
            $hasScore = true;
        }

        return $hasScore ? round($total, 2) : null;
    }

    public static function normalize(?float $score): ?float
    {
        if ($score === null) {
            return null;
        }

        $numeric = (float) $score;

        if ($numeric > 100 && $numeric <= 10000) {
            $numeric /= 100;
        }

        return $numeric;
    }

    public static function letterGrade(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        $normalized = self::normalize((float) $score);
        if ($normalized === null) {
            return null;
        }

        if ($normalized < 0) {
            return null;
        }

        $ranges = [
            ['min' => 80.0, 'max' => 100.00001, 'grade' => 'A'],
            ['min' => 75.0, 'max' => 79.99999, 'grade' => 'AB'],
            ['min' => 70.0, 'max' => 74.99999, 'grade' => 'B'],
            ['min' => 65.0, 'max' => 69.99999, 'grade' => 'BC'],
            ['min' => 60.0, 'max' => 64.99999, 'grade' => 'C'],
            ['min' => 55.0, 'max' => 59.99999, 'grade' => 'CD'],
            ['min' => 50.0, 'max' => 54.99999, 'grade' => 'D'],
            ['min' => 45.0, 'max' => 49.99999, 'grade' => 'DE'],
            ['min' => 0.0,  'max' => 44.99999, 'grade' => 'E'],
        ];

        foreach ($ranges as $range) {
            if ($normalized >= $range['min'] && $normalized <= $range['max']) {
                return $range['grade'];
            }
        }

        return null;
    }

    /**
     * Build a textual description of how weighted components are combined.
     *
     * @param array $components Each component can include label, value/score, weight, and/or weight_percent.
     * @param float|null $result The final result to append at the end of the expression.
     * @param int $decimals Number of decimals for rendered numbers.
     * @param bool $autoDenominator Whether to append an automatic denominator (percent sum/count) when weights exist.
     * @return array{
     *     parts: array<int,array<string,mixed>>,
     *     expression: ?string,
     *     expression_parts: array<int,string>,
     *     denominator: ?string,
     *     result: ?float,
     *     total_weight_percent: ?float
     * }
     */
    public static function describeWeightedFormula(array $components, ?float $result = null, int $decimals = 2, bool $autoDenominator = true): array
    {
        $parts = [];
        $expressionParts = [];
        $totalWeightPercent = 0.0;
        $totalWeightFraction = 0.0;

        foreach (array_values($components) as $index => $component) {
            $label = trim((string)($component['label'] ?? ('Komponen ' . ($index + 1))));
            $value = null;
            if (isset($component['value'])) {
                $value = (float)$component['value'];
            } elseif (isset($component['score'])) {
                $value = (float)$component['score'];
            }
            if ($value === null) {
                continue;
            }

            $weightPercent = null;
            $weightFraction = null;
            if (isset($component['weight_percent'])) {
                $weightPercent = (float)$component['weight_percent'];
                $weightFraction = $weightPercent / 100;
            } elseif (isset($component['weight'])) {
                $rawWeight = (float)$component['weight'];
                if ($rawWeight > 1) {
                    $weightPercent = $rawWeight;
                    $weightFraction = $rawWeight / 100;
                } elseif ($rawWeight > 0) {
                    $weightFraction = $rawWeight;
                    $weightPercent = $rawWeight * 100;
                }
            }

            if ($weightFraction !== null) {
                $totalWeightFraction += $weightFraction;
                if ($weightPercent !== null) {
                    $totalWeightPercent += $weightPercent;
                }
            }

            $weightText = null;
            if ($weightPercent !== null) {
                $weightText = rtrim(rtrim(number_format($weightPercent, 2), '0'), '.') . '%';
            }

            $valueText = number_format($value, $decimals);
            $partText = $weightText
                ? sprintf('%s %s × %s', $label, $valueText, $weightText)
                : sprintf('%s %s', $label, $valueText);

            $parts[] = [
                'label' => $label,
                'value' => round($value, $decimals),
                'weight_percent' => $weightPercent !== null ? round($weightPercent, 2) : null,
                'weight_fraction' => $weightFraction,
                'text' => $partText,
            ];
            $expressionParts[] = $partText;
        }

        $expression = null;
        $denominatorLabel = null;
        if (!empty($expressionParts)) {
            $expression = '(' . implode(' + ', $expressionParts) . ')';
            if ($autoDenominator) {
                if ($totalWeightPercent > 0) {
                    $denominatorLabel = rtrim(rtrim(number_format($totalWeightPercent, 2), '0'), '.') . '%';
                } elseif ($totalWeightFraction > 0) {
                    $denominatorLabel = rtrim(rtrim(number_format($totalWeightFraction, 4), '0'), '.');
                } elseif (count($expressionParts) > 1) {
                    $denominatorLabel = (string)count($expressionParts);
                }
                if ($denominatorLabel !== null) {
                    $expression .= ' / ' . $denominatorLabel;
                }
            }
            if ($result !== null) {
                $expression .= ' = ' . number_format($result, $decimals);
            }
        }

        return [
            'parts' => $parts,
            'expression' => $expression,
            'expression_parts' => $expressionParts,
            'denominator' => $denominatorLabel,
            'result' => $result !== null ? round($result, $decimals) : null,
            'total_weight_percent' => $totalWeightPercent > 0 ? round($totalWeightPercent, 2) : null,
        ];
    }
}
