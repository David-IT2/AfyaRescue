<?php

namespace App\Services;

/**
 * Advanced triage: weighted scoring for symptoms, categorizes as Critical / Moderate / Mild.
 * Weights: chest pain, bleeding, unconsciousness, stroke signs, breathing difficulty.
 */
class TriageService
{
    public const CATEGORY_CRITICAL = 'Critical';
    public const CATEGORY_MODERATE = 'Moderate';
    public const CATEGORY_MILD = 'Mild';

    /** Weighted points per symptom (higher = more severe). */
    private const WEIGHTS = [
        'unconsciousness' => 5,   // not conscious
        'breathing_difficulty' => 4, // difficult=2, absent=5 (handled in options)
        'bleeding' => 4,         // severe=4, minor=1, none=0
        'stroke_symptoms' => 4,
        'chest_pain' => 3,
        'allergic_reaction' => 3,
        'pregnancy_emergency' => 2,
        'number_of_casualties' => 1, // per casualty, max 3
    ];

    private const BREATHING_WEIGHTS = ['normal' => 0, 'difficult' => 2, 'absent' => 5];
    private const BLEEDING_WEIGHTS = ['none' => 0, 'minor' => 1, 'severe' => 4];

    public function calculateWeightedScore(array $responses): int
    {
        $score = 0;
        $conscious = $responses['conscious'] ?? true;
        if ($conscious === false || $conscious === '0' || $conscious === 0) {
            $score += self::WEIGHTS['unconsciousness'];
        }
        $breathing = $responses['breathing'] ?? 'normal';
        $score += self::BREATHING_WEIGHTS[$breathing] ?? 0;
        $bleeding = $responses['bleeding'] ?? 'none';
        $score += self::BLEEDING_WEIGHTS[$bleeding] ?? 0;
        if (! empty($responses['stroke_symptoms'])) {
            $score += self::WEIGHTS['stroke_symptoms'];
        }
        if (! empty($responses['chest_pain'])) {
            $score += self::WEIGHTS['chest_pain'];
        }
        if (! empty($responses['allergic_reaction'])) {
            $score += self::WEIGHTS['allergic_reaction'];
        }
        if (! empty($responses['pregnancy_emergency'])) {
            $score += self::WEIGHTS['pregnancy_emergency'];
        }
        $casualties = (int) ($responses['number_of_casualties'] ?? 0);
        $score += min($casualties * self::WEIGHTS['number_of_casualties'], 3);
        return min(25, max(0, $score));
    }

    /** Map weighted score to category: Critical, Moderate, Mild. */
    public function scoreToCategory(int $score): string
    {
        if ($score >= 10) {
            return self::CATEGORY_CRITICAL;
        }
        if ($score >= 4) {
            return self::CATEGORY_MODERATE;
        }
        return self::CATEGORY_MILD;
    }

    /** Legacy 0–10 score for backward compatibility (normalized from weighted). */
    public function scoreToLegacyScale(int $weightedScore): int
    {
        if ($weightedScore >= 10) {
            return min(10, 9 + (int) (($weightedScore - 10) / 3));
        }
        if ($weightedScore >= 4) {
            return (int) (3 + ($weightedScore - 4) * 1.2);
        }
        return (int) ($weightedScore * 0.75);
    }

    /** Legacy label: critical, high, medium, low. */
    public function categoryToLegacyLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_CRITICAL => 'critical',
            self::CATEGORY_MODERATE => 'medium',
            self::CATEGORY_MILD => 'low',
            default => 'low',
        };
    }

    /** Full triage result: score, category, legacy score/label for storage. */
    public function evaluate(array $responses): array
    {
        $weightedScore = $this->calculateWeightedScore($responses);
        $category = $this->scoreToCategory($weightedScore);
        $legacyScore = $this->scoreToLegacyScale($weightedScore);
        $legacyLabel = $this->categoryToLegacyLabel($category);
        return [
            'weighted_score' => $weightedScore,
            'category' => $category,
            'legacy_score' => min(10, $legacyScore),
            'legacy_label' => $legacyLabel,
        ];
    }
}
