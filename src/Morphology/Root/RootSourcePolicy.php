<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

final class RootSourcePolicy
{
    private const TRUSTED_RULE_MIN_CONFIDENCE = 0.90;

    public function priority(string $source): int
    {
        return match (true) {
            $source === 'manual_lexicon' => 100,
            $source === 'database' => 95,

            str_starts_with($source, 'rule:masdar_') => 85,
            str_starts_with($source, 'rule:active_participle_') => 85,
            str_starts_with($source, 'rule:passive_participle_') => 85,
            str_starts_with($source, 'rule:instrument_') => 85,
            str_starts_with($source, 'rule:place_time_') => 85,
            str_starts_with($source, 'rule:action_state_') => 85,
            str_starts_with($source, 'rule:instance_manner_') => 85,

            str_starts_with($source, 'rule:verb_') => 82,

            str_starts_with($source, 'rule:adjective_') => 78,
            str_starts_with($source, 'rule:exaggeration_') => 78,
            str_starts_with($source, 'rule:relative_nisba_') => 76,
            str_starts_with($source, 'rule:broken_plural_') => 70,

            str_starts_with($source, 'rule:nominal_') => 76,
            str_starts_with($source, 'rule:') => 75,

            $source === 'database_alternative' => 60,
            $source === 'scale' => 40,
            $source === 'fallback_core' => 10,

            default => 50,
        };
    }

    public function isAuthoritative(string $source, float $confidence): bool
    {
        return match (true) {
            $source === 'manual_lexicon',
            $source === 'database' => $confidence >= 0.80,

            // Explicit measurable adjective أفعل مثل أكبر should remain reliable.
            $source === 'rule:adjective_af3l' => $confidence >= 0.85,

            str_starts_with($source, 'rule:') => $confidence >= self::TRUSTED_RULE_MIN_CONFIDENCE,

            default => false,
        };
    }

    public function isFallback(string $source): bool
    {
        return in_array($source, [
            'scale',
            'fallback_core',
            'database_alternative',
        ], true);
    }
}
