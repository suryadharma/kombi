<?php

class Settings
{
    private static array $cache = [];

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        try {
            $database = new Database();
            $db = $database->getConnection();
            $query = "SELECT value FROM settings WHERE key_name = :key LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                self::$cache[$key] = $row['value'];
                return $row['value'];
            }
        } catch (Exception $e) {
            error_log('Settings::get error: ' . $e->getMessage());
        }

        return $default;
    }

    public static function getPembimbingRatio(): array
    {
        $ratio = self::get('pembimbing_ratio', '60:40');
        [$p1, $p2] = array_pad(array_map('intval', explode(':', $ratio)), 2, 0);
        $total = max($p1 + $p2, 1);
        return [$p1 / $total, $p2 / $total];
    }

    public static function getPengujiAggregationMethod(): string
    {
        return self::get('penguji_method', 'rata-rata');
    }
    
    public static function getActiveAngkatan(): array
    {
        $activeAngkatan = self::get('active_angkatan', '');
        if (empty($activeAngkatan)) {
            return [];
        }

        $angkatanList = array_map('trim', explode(',', $activeAngkatan));
        $angkatanList = array_filter($angkatanList, static function ($item) {
            return $item !== '' && ctype_digit($item);
        });

        $angkatanList = array_map('intval', $angkatanList);

        return array_values(array_unique($angkatanList));
    }
    
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    public static function getSlaThresholdForStage(string $stage, int $default = 72): int
    {
        $normalized = str_replace('-', '_', strtolower($stage));
        $key = 'sla_' . $normalized . '_hours';
        $value = self::get($key);
        if ($value === null || $value === '') {
            $fallback = self::get('sla_threshold_hours', $default);
            return (int) $fallback ?: $default;
        }
        return max(1, (int) $value);
    }

    public static function getSlaThresholds(): array
    {
        $stages = ['sempro', 'semhas', 'pra-ujian', 'ujian'];
        $thresholds = [];
        foreach ($stages as $stage) {
            $thresholds[$stage] = self::getSlaThresholdForStage($stage, 72);
        }
        return $thresholds;
    }

    public static function getStageProgressSla(string $fromStage, string $toStage, int $default = 30): int
    {
        $from = str_replace('-', '_', strtolower($fromStage));
        $to = str_replace('-', '_', strtolower($toStage));
        $key = sprintf('sla_%s_to_%s_days', $from, $to);
        $value = self::get($key);
        if ($value === null || $value === '') {
            return max(1, $default);
        }
        return max(1, (int) $value);
    }

    public static function getStageProgressSlaConfig(): array
    {
        $transitions = [
            'sempro_semhas' => ['from' => 'sempro', 'to' => 'semhas', 'default' => 30],
            'semhas_pra-ujian' => ['from' => 'semhas', 'to' => 'pra-ujian', 'default' => 30],
            'pra-ujian_ujian' => ['from' => 'pra-ujian', 'to' => 'ujian', 'default' => 30],
        ];

        $config = [];
        foreach ($transitions as $key => $transition) {
            $config[$key] = self::getStageProgressSla(
                $transition['from'],
                $transition['to'],
                $transition['default']
            );
        }

        return $config;
    }

    public static function getScoreVisibilityConfig(): array
    {
        $stages = ['sempro', 'semhas', 'pra-ujian', 'ujian', 'final_score'];
        $config = [];
        foreach ($stages as $stage) {
            if ($stage === 'final_score') {
                $config[$stage] = self::isFinalScoreVisible();
            } else {
                $config[$stage] = self::isScoreVisibleForStage($stage);
            }
        }
        return $config;
    }

    public static function isScoreVisibleForStage(string $stage): bool
    {
        $normalized = str_replace('-', '_', strtolower($stage));
        $value = self::get('score_visibility_' . $normalized, '0');
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }
        return !empty($value);
    }

    public static function isFinalScoreVisible(): bool
    {
        $value = self::get('score_visibility_final_score', '0');
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }
        return !empty($value);
    }

    /**
     * Get evaluation edit window duration in hours
     */
    public static function getEvaluationEditWindowHours(): int
    {
        $value = self::get('evaluation_edit_window_hours', '48');
        return (int) $value;
    }

    /**
     * Check if student should be notified when score is edited
     */
    public static function shouldNotifyStudentOnScoreEdit(): bool
    {
        $value = self::get('notify_student_on_score_edit', 'false');
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }
        return !empty($value);
    }

    /**
     * Check if an evaluation is still editable
     */
    public static function isEvaluationEditable(?string $submittedAt, ?string $lockedAt): bool
    {
        // If locked, cannot edit
        if ($lockedAt !== null) {
            return false;
        }

        // If never submitted, can edit
        if ($submittedAt === null) {
            return true;
        }

        // Check if within edit window
        $editWindowHours = self::getEvaluationEditWindowHours();
        $submittedTime = strtotime($submittedAt);
        $expireTime = $submittedTime + ($editWindowHours * 3600); // Convert hours to seconds
        $currentTime = time();

        return $currentTime < $expireTime;
    }
}
