<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

final class RootCandidateRanker
{
    /**
     * @param  list<RootCandidate>  $candidates
     * @return list<RootCandidate>
     */
    public function rank(array $candidates): array
    {
        $unique = [];

        foreach ($candidates as $candidate) {
            $key = $candidate->root.'|'.$candidate->source;

            if (
                ! isset($unique[$key])
                || $candidate->confidence > $unique[$key]->confidence
            ) {
                $unique[$key] = $candidate;
            }
        }

        $ranked = array_values($unique);

        usort(
            $ranked,
            static function (RootCandidate $a, RootCandidate $b): int {
                $priority = self::sourcePriority($b->source) <=> self::sourcePriority($a->source);

                if ($priority !== 0) {
                    return $priority;
                }

                return $b->confidence <=> $a->confidence;
            }
        );

        return $ranked;
    }

    /**
     * @param  list<RootCandidate>  $candidates
     */
    public static function best(array $candidates): ?RootCandidate
    {
        $ranked = (new self)->rank($candidates);

        return $ranked[0] ?? null;
    }

    private static function sourcePriority(string $source): int
    {
        static $policy = null;

        $policy ??= new RootSourcePolicy;

        return $policy->priority($source);
    }
}
