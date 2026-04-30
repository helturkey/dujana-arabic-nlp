<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root;

use Dujana\ArabicNlp\Candidates\CoreCandidate;

final class FallbackRootExtractor
{
    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $core = $candidate->core;

        if ($core === '') {
            return [];
        }

        return [
            new RootCandidate(
                root: $core,
                confidence: 0.10,
                source: 'fallback_core',
                reasons: [
                    'fallback:no_reliable_root_candidate',
                    'fallback:returned_core_as_safe_unreliable_value',
                    'fallback:not_authoritative',
                    'not_authoritative_root',
                    'normalized:'.$candidate->normalized,
                    'core:'.$candidate->core,
                    'fallback:root_rules_exhausted',
                ],
            ),
        ];
    }
}
