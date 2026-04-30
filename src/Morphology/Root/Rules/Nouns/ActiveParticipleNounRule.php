<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class ActiveParticipleNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractFaa3l(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractMf3l($word));

            array_push($roots, ...$this->extractMstf3l($word));
            array_push($roots, ...$this->extractMtf3l($word));
            array_push($roots, ...$this->extractMtfaa3l($word));
            array_push($roots, ...$this->extractMnf3l($word));
            array_push($roots, ...$this->extractMft3l($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * مُفعل من أفعل:
     * مكرم، مخرج، مدخل، محسن، معلن => كرم، خرج، دخل، حسن، علن
     *
     * In unvocalized Arabic this overlaps with place/time noun مفعل:
     * مخرج، مدخل، مجلس...
     *
     * @return list<RootCandidate>
     */
    private function extractMf3l(string $word): array
    {
        $surface = $this->surfaceShape($word);

        if ($this->hasDerivedMeemPrefix($surface) || str_starts_with($surface, 'من')) {
            return [];
        }

        if (preg_match('/^م([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.78,
                source: 'rule:active_participle_mf3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:مفعل',
                    'pattern:mf3l',
                    'rule:remove_prefix_م',
                    'morphology:active_participle_noun',
                    'caution:ambiguous_active_participle_or_place_time_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فاعل:
     * كاتب => كتب
     * عالم => علم
     * فاتح => فتح
     *
     * Conservative: strong triliteral only.
     *
     * @return list<RootCandidate>
     */
    private function extractFaa3l(string $word, string $originalSurface): array
    {
        // Hamza-heavy surfaces such as مآخذ، قارئ، سائل are lexical/hamza cases.
        if ($this->containsHamza($word) || $this->containsHamza($originalSurface)) {
            return [];
        }

        // قاتَلَ / شارَكَ / باعَدَ are verbs, not active participles.
        if (
            $this->hasNonShaddaHaraka($originalSurface)
            && preg_match('/ا[\p{Arabic}]َ[\p{Arabic}]َ?/u', $originalSurface) === 1
        ) {
            return [];
        }

        if (preg_match('/^([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.84,
                source: 'rule:active_participle_faa3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:فاعل',
                    'pattern:faa3l',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:active_participle_noun',
                    'caution:ambiguous_active_participle_pattern',
                ],
            ),
        ];
    }

    /**
     * مستفعل:
     * مستخرج => خرج
     * مستعمل => عمل
     * مستقبل => قبل
     *
     * @return list<RootCandidate>
     */
    private function extractMstf3l(string $word): array
    {
        if (preg_match('/^مست([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.95,
                source: 'rule:active_participle_mstf3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:مستفعل',
                    'pattern:mstf3l',
                    'rule:remove_prefix_مست',
                    'morphology:active_participle_noun',
                ],
            ),
        ];
    }

    /**
     * متفعّل / متفعل after normalization:
     * متعلم => علم
     * متكبر => كبر
     * متحسن => حسن
     *
     * Conservative unvocalized support.
     *
     * @return list<RootCandidate>
     */
    private function extractMtf3l(string $word): array
    {
        if (preg_match('/^مت([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.91,
                source: 'rule:active_participle_mtf3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:متفعّل',
                    'pattern:mtf3l',
                    'rule:remove_prefix_مت',
                    'morphology:active_participle_noun',
                    'caution:unvocalized_tf3lla_participle',
                ],
            ),
        ];
    }

    /**
     * متفاعل:
     * متقاتل => قتل
     * متشارك => شرك
     * متباعد => بعد
     *
     * @return list<RootCandidate>
     */
    private function extractMtfaa3l(string $word): array
    {
        if (preg_match('/^مت([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.93,
                source: 'rule:active_participle_mtfaa3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:متفاعل',
                    'pattern:mtfaa3l',
                    'rule:remove_prefix_مت',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:active_participle_noun',
                ],
            ),
        ];
    }

    /**
     * منفعل:
     * منكسر => كسر
     * منفتح => فتح
     * منقطع => قطع
     *
     * @return list<RootCandidate>
     */
    private function extractMnf3l(string $word): array
    {
        if (preg_match('/^من([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.93,
                source: 'rule:active_participle_mnf3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:منفعل',
                    'pattern:mnf3l',
                    'rule:remove_prefix_من',
                    'morphology:active_participle_noun',
                ],
            ),
        ];
    }

    /**
     * مفتعل:
     * مجتمع => جمع
     * مقترب => قرب
     * مختلف => خلف
     *
     * @return list<RootCandidate>
     */
    private function extractMft3l(string $word): array
    {
        if (preg_match('/^م([\p{Arabic}])ت([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.92,
                source: 'rule:active_participle_mft3l',
                reasons: [
                    'rule_family:active_participle',
                    'form:مفتعل',
                    'pattern:mft3l',
                    'rule:remove_prefix_م',
                    'rule:remove_infix_ta_after_first_radical',
                    'morphology:active_participle_noun',
                    'caution:ambiguous_augmented_participle_pattern',
                ],
            ),
        ];
    }
}
