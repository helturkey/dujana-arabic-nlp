<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class ActionStateNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractFu3l(
                originalSurface: $candidate->originalSurface,
            ));
            array_push($roots, ...$this->extractFa3alaan(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractFu3laan(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractFi3laan(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * فَعَلان:
     * نَقَصان، رَجَفان، خَفَقان => نقص، رجف، خفق
     *
     * Weak-aware:
     * غَلَيان => غلي
     * جَرَيان => جري
     * دَوَران => دور
     *
     * Requires visible haraka evidence because unvocalized surfaces are broad.
     *
     * @return list<RootCandidate>
     */
    private function extractFa3alaan(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        /*
         * Require فَعَلان-like evidence on the original surface:
         * نَقَصان، غَلَيان، دَوَران
         */
        if (preg_match('/^([\p{Arabic}])َ([\p{Arabic}])َ[\p{Arabic}]ان$/u', $originalSurface) !== 1) {
            return [];
        }

        $surface = $this->surfaceShape($originalSurface);

        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ان$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        $isWeak = preg_match('/[وي]/u', $root) === 1;

        return [
            new RootCandidate(
                root: $root,
                confidence: $isWeak ? 0.78 : 0.82,
                source: $isWeak
                    ? 'rule:action_state_fa3alaan_weak'
                    : 'rule:action_state_fa3alaan',
                reasons: [
                    'rule_family:action_state',
                    'form:فَعَلان',
                    'pattern:fa3alaan',
                    'rule:requires_visible_fatha_evidence',
                    'rule:remove_suffix_ان',
                    $isWeak ? 'morphology:weak_action_or_state_noun' : 'morphology:action_or_state_noun',
                    $isWeak ? 'caution:weak_root_action_state_pattern' : 'caution:ambiguous_action_state_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فُعْلان:
     * غُفْران، خُسْران، رُجْحان، نُقْصان => غفر، خسر، رجح، نقص
     *
     * Requires visible damma/sukun evidence because unvocalized surfaces are broad.
     *
     * @return list<RootCandidate>
     */
    private function extractFu3laan(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        /*
         * Require فُعْلان-like evidence:
         * R1 + damma + R2 + optional sukun + R3 + ا + ن
         */
        if (preg_match('/^([\p{Arabic}])ُ([\p{Arabic}])ْ?([\p{Arabic}])ان$/u', $originalSurface) !== 1) {
            return [];
        }

        $surface = $this->surfaceShape($originalSurface);

        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ان$/u', $surface, $m) !== 1) {
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
                source: 'rule:action_state_fu3laan',
                reasons: [
                    'rule_family:action_state',
                    'form:فُعْلان',
                    'pattern:fu3laan',
                    'rule:requires_visible_damma_sukun_evidence',
                    'rule:remove_suffix_ان',
                    'morphology:action_or_state_noun',
                    'caution:ambiguous_action_state_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فِعْلان:
     * حِرْمان، وِجْدان، نِسْيان => حرم، وجد، نسي
     *
     * Requires visible kasra/sukun evidence because unvocalized surfaces are broad.
     *
     * @return list<RootCandidate>
     */
    private function extractFi3laan(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        /*
         * Require فِعْلان-like evidence:
         * R1 + kasra + R2 + optional sukun + R3 + ا + ن
         */
        if (preg_match('/^([\p{Arabic}])ِ([\p{Arabic}])ْ?([\p{Arabic}])ان$/u', $originalSurface) !== 1) {
            return [];
        }

        $surface = $this->surfaceShape($originalSurface);

        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ان$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.79,
                source: 'rule:action_state_fi3laan',
                reasons: [
                    'rule_family:action_state',
                    'form:فِعْلان',
                    'pattern:fi3laan',
                    'rule:requires_visible_kasra_sukun_evidence',
                    'rule:remove_suffix_ان',
                    'morphology:action_or_state_noun',
                    'caution:ambiguous_action_state_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فُعْل:
     * جُرْحٌ، حُزْنٌ، شُكْرٌ، كُفْرٌ => جرح، حزن، شكر، كفر
     *
     * Requires visible damma/sukun evidence because unvocalized فعل is identical
     * to a bare triliteral surface.
     *
     * @return list<RootCandidate>
     */
    private function extractFu3l(string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        /*
         * Accepts:
         * جُرْحٌ
         * جُرْح
         * حُزْنٌ
         * شُكْرٌ
         *
         * Pattern:
         * R1 + damma + R2 + optional sukun + R3 + optional tanwin/damma
         */
        if (preg_match('/^([\p{Arabic}])ُ([\p{Arabic}])ْ?([\p{Arabic}])[\x{064B}\x{064C}\x{064D}]?$/u', $originalSurface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.86,
                source: 'rule:action_state_fu3l',
                reasons: [
                    'rule_family:action_state',
                    'form:فُعْل',
                    'pattern:fu3l',
                    'rule:requires_visible_damma_sukun_evidence',
                    'rule:surface_equals_root_after_harakat_removed',
                    'morphology:action_or_state_noun',
                    'caution:surface_identical_to_bare_triliteral_root_when_unvocalized',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
