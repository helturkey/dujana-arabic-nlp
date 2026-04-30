<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Classification;

use Dujana\ArabicNlp\Enums\WordKindEnum;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;

final class WordKindFromRootSourceResolver
{
    public function resolve(?RootCandidate $candidate): ?WordKindEnum
    {
        if ($candidate === null) {
            return null;
        }

        $source = $candidate->source;

        return match (true) {
            str_starts_with($source, 'rule:verb_') => WordKindEnum::Verb,

            str_starts_with($source, 'rule:masdar_'),
            str_starts_with($source, 'rule:active_participle_'),
            str_starts_with($source, 'rule:passive_participle_'),
            str_starts_with($source, 'rule:place_time_'),
            str_starts_with($source, 'rule:instrument_'),
            str_starts_with($source, 'rule:adjective_'),
            str_starts_with($source, 'rule:exaggeration_'),
            str_starts_with($source, 'rule:instance_manner_'),
            str_starts_with($source, 'rule:action_state_'),
            str_starts_with($source, 'rule:relative_nisba_'),
            str_starts_with($source, 'rule:nominal_'),
            str_starts_with($source, 'rule:broken_plural_') => WordKindEnum::Noun,

            default => null,
        };
    }
}
