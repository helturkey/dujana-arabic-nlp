<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class InstanceMannerNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractF3lah(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractFi3lah(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * فَعْلة:
     * ضَرْبة، جَلْسة، نَظْرة => ضرب، جلس، نظر
     *
     * Requires visible haraka evidence because unvocalized فعلة is very broad.
     *
     * @return list<RootCandidate>
     */
    private function extractF3lah(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        $surface = $this->surfaceShape($originalSurface);

        if (! str_ends_with($surface, 'ة')) {
            return [];
        }

        /*
         * Strict vocalized shape:
         * R1 + fatha + R2 + optional sukun + R3 + optional short vowel + ة
         *
         * Examples:
         * ضَرْبة، جَلْسة، نَظْرة
         */
        if (preg_match('/^([\p{Arabic}])َ([\p{Arabic}])ْ?([\p{Arabic}])[\x{064B}-\x{0652}]?ة$/u', $originalSurface, $m) !== 1) {
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
                source: 'rule:instance_manner_f3lah',
                reasons: [
                    'rule_family:instance_manner',
                    'form:فَعْلة',
                    'pattern:f3lah',
                    'rule:requires_visible_fatha_sukun_evidence',
                    'rule:remove_ta_marbuta',
                    'morphology:instance_or_manner_noun',
                    'caution:ambiguous_instance_or_common_noun_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فِعْلة:
     * جِلْسة، نِظْرة، ضِحْكة => جلس، نظر، ضحك
     *
     * Requires visible kasra evidence because unvocalized فعلة is very broad.
     *
     * @return list<RootCandidate>
     */
    private function extractFi3lah(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        $surface = $this->surfaceShape($originalSurface);

        if (! str_ends_with($surface, 'ة')) {
            return [];
        }

        /*
         * Strict vocalized shape:
         * R1 + kasra + R2 + optional sukun + R3 + optional short vowel + ة
         *
         * Examples:
         * جِلْسة، نِظْرة، ضِحْكة
         */
        if (preg_match('/^([\p{Arabic}])ِ([\p{Arabic}])ْ?([\p{Arabic}])[\x{064B}-\x{0652}]?ة$/u', $originalSurface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.80,
                source: 'rule:instance_manner_fi3lah',
                reasons: [
                    'rule_family:instance_manner',
                    'form:فِعْلة',
                    'pattern:fi3lah',
                    'rule:requires_visible_kasra_sukun_evidence',
                    'rule:remove_ta_marbuta',
                    'morphology:manner_noun',
                    'caution:ambiguous_manner_or_common_noun_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
