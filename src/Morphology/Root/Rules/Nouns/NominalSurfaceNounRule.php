<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class NominalSurfaceNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        return $this->uniqueRootCandidates([
            ...$this->extractF3aal($candidate->originalSurface),
        ]);
    }

    /**
     * فعال:
     * كتاب => كتب
     * حساب => حسب
     *
     * Conservative nominal surface-pattern root candidate.
     *
     * @return list<RootCandidate>
     */
    private function extractF3aal(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.82,
                source: 'rule:nominal_f3aal',
                reasons: [
                    'rule_family:nominal',
                    'form:فعال',
                    'pattern:f3aal',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:nominal_surface_pattern',
                    'caution:surface_pattern_root_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
