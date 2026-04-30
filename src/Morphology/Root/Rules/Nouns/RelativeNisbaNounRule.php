<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class RelativeNisbaNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        return $this->uniqueRootCandidates([
            ...$this->extractNisbaYaa($candidate->originalSurface),
            ...$this->extractFeminineNisba($candidate->originalSurface),
            ...$this->extractExtendedFeminineNisba($candidate->originalSurface),
        ]);
    }

    /**
     * نسبة بالياء:
     * مصري، عربي، مدني => مصر، عرب، مدن
     *
     * Very conservative:
     * only 4-letter surfaces ending with ي, resulting in a triliteral base.
     *
     * @return list<RootCandidate>
     */
    private function extractNisbaYaa(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if (! str_ends_with($surface, 'ي')) {
            return [];
        }

        if (mb_strlen($surface) !== 4) {
            return [];
        }

        $root = mb_substr($surface, 0, 3);

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.58,
                source: 'rule:relative_nisba_y',
                reasons: [
                    'rule_family:nisba',
                    'form:نسبة',
                    'pattern:f3ly',
                    'rule:remove_final_nisba_ya',
                    'morphology:relative_adjective_or_noun',
                    'caution:nisba_surface_is_highly_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * نسبة مؤنثة:
     * مصرية، عربية، مدنية => مصر، عرب، مدن
     *
     * Very conservative:
     * only 5-letter surfaces ending with ية, resulting in a triliteral base.
     *
     * @return list<RootCandidate>
     */
    private function extractFeminineNisba(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if (! str_ends_with($surface, 'ية')) {
            return [];
        }

        if (mb_strlen($surface) !== 5) {
            return [];
        }

        $root = mb_substr($surface, 0, 3);

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.57,
                source: 'rule:relative_nisba_feminine',
                reasons: [
                    'rule_family:nisba',
                    'form:نسبة مؤنثة',
                    'pattern:f3lyة',
                    'rule:remove_final_nisba_ya_ta_marbuta',
                    'morphology:feminine_relative_adjective_or_noun',
                    'caution:nisba_surface_is_highly_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * Extended feminine nisba:
     * عباسية => عباس
     * بغدادية => بغداد
     *
     * This is not necessarily a triliteral root extraction; it is a conservative
     * base extraction for relative nouns/adjectives.
     *
     * @return list<RootCandidate>
     */
    private function extractExtendedFeminineNisba(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if (! str_ends_with($surface, 'ية')) {
            return [];
        }

        if (mb_strlen($surface) < 6) {
            return [];
        }

        $base = mb_substr($surface, 0, -2);

        if (mb_strlen($base) < 4) {
            return [];
        }

        if ($this->containsHamza($base)) {
            return [];
        }

        /*
         * Avoid weak-heavy / unclear bases for now.
         * Examples like أموية، علوية، قروية should be handled later with special rules.
         */
        if (preg_match('/[وىي]/u', $base) === 1) {
            return [];
        }

        return [
            new RootCandidate(
                root: $base,
                confidence: 0.46,
                source: 'rule:relative_nisba_feminine_extended',
                reasons: [
                    'rule_family:nisba',
                    'form:نسبة مؤنثة',
                    'pattern:base+ية',
                    'rule:remove_final_nisba_ya_ta_marbuta',
                    'morphology:extended_feminine_relative_adjective_or_noun',
                    'caution:not_strict_root_extraction_base_only',
                    'caution:nisba_surface_is_highly_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
