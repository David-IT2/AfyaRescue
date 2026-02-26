<?php

namespace App\Services;

/**
 * Rule-based triage severity scoring from questionnaire responses.
 * Score 0-10; label: low (0-2), medium (3-5), high (6-8), critical (9-10).
 */
class TriageScoringService
{
    public const LABEL_LOW = 'low';
    public const LABEL_MEDIUM = 'medium';
    public const LABEL_HIGH = 'high';
    public const LABEL_CRITICAL = 'critical';

    /** Default questionnaire keys used by the form and expected in responses. */
    public static function questionKeys(): array
    {
        return [
            'conscious' => ['type' => 'boolean', 'critical_false' => 4],
            'breathing' => ['type' => 'string', 'options' => ['normal' => 0, 'difficult' => 2, 'absent' => 5]],
            'bleeding' => ['type' => 'string', 'options' => ['none' => 0, 'minor' => 1, 'severe' => 4]],
            'chest_pain' => ['type' => 'boolean', 'critical_true' => 2],
            'stroke_symptoms' => ['type' => 'boolean', 'critical_true' => 3],
            'pregnancy_emergency' => ['type' => 'boolean', 'critical_true' => 2],
            'allergic_reaction' => ['type' => 'boolean', 'critical_true' => 2],
            'number_of_casualties' => ['type' => 'integer', 'max_score' => 2],
        ];
    }

    public function calculateScore(array $responses): int
    {
        $score = 0;
        $rules = self::questionKeys();

        foreach ($responses as $key => $value) {
            if (! isset($rules[$key])) {
                continue;
            }
            $rule = $rules[$key];
            if (isset($rule['critical_false']) && $rule['type'] === 'boolean') {
                if ($value === false) {
                    $score += $rule['critical_false'];
                }
            } elseif (isset($rule['critical_true']) && $rule['type'] === 'boolean') {
                if ($value === true) {
                    $score += $rule['critical_true'];
                }
            } elseif (isset($rule['options']) && is_string($value)) {
                $score += $rule['options'][$value] ?? 0;
            } elseif (isset($rule['max_score']) && is_numeric($value)) {
                $score += min((int) $value * 1, $rule['max_score']);
            }
        }

        return min(10, max(0, $score));
    }

    public function scoreToLabel(int $score): string
    {
        if ($score >= 9) {
            return self::LABEL_CRITICAL;
        }
        if ($score >= 6) {
            return self::LABEL_HIGH;
        }
        if ($score >= 3) {
            return self::LABEL_MEDIUM;
        }
        return self::LABEL_LOW;
    }
}
