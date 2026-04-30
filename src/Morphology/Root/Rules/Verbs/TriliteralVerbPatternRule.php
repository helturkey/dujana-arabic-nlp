<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Verbs;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class TriliteralVerbPatternRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractF3ll($word));
            array_push($roots, ...$this->extractYf3ll($word));

            array_push($roots, ...$this->extractF33l($word));
            array_push($roots, ...$this->extractYf33l($word));

            array_push($roots, ...$this->extractYfa3l($word));

            array_push($roots, ...$this->extractAf3la($word));

            array_push($roots, ...$this->extractFa3la(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractYf3l($word));
            array_push($roots, ...$this->extractF3l($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * أفعل:
     * أكرم، أخرج، أدخل، أحسن => كرم، خرج، دخل، حسن
     *
     * @return list<RootCandidate>
     */
    private function extractAf3la(string $word): array
    {
        if (preg_match('/^[اأإآ]([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.94,
                source: 'rule:verb_triliteral_af3la',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:أفعل',
                    'pattern:af3la',
                    'rule:remove_initial_hamza_or_alif',
                    'morphology:augmented_triliteral_verb',
                ],
            ),
        ];
    }

    /**
     * فاعَلَ:
     * قاتَلَ، شارَكَ، باعَدَ، جادَلَ => قتل، شرك، بعد، جدل
     *
     * Requires visible haraka evidence because the unvocalized surface overlaps with
     * active participle فاعل:
     * قاتل، كاتب، عالم...
     *
     * @return list<RootCandidate>
     */
    private function extractFa3la(string $word, string $originalSurface): array
    {
        if ($this->containsHamza($word) || $this->containsHamza($originalSurface)) {
            return [];
        }

        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        // Require past-tense fatha evidence somewhere after the pattern alif:
        // قاتَلَ، شارَكَ، باعَدَ، جادَلَ
        if (preg_match('/ا[\p{Arabic}]َ[\p{Arabic}]َ?/u', $originalSurface) !== 1) {
            return [];
        }

        $surface = $this->surfaceShape($originalSurface);

        if (preg_match('/^([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $surface, $m) !== 1) {
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
                source: 'rule:verb_triliteral_fa3la',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:فاعَلَ',
                    'pattern:fa3la',
                    'rule:requires_visible_fatha_evidence',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:augmented_triliteral_verb',
                ],
            ),
        ];
    }

    /**
     * فعلّ / الأجوف المضعّف المختصر:
     * مدّ، شدّ، ردّ، عدّ، فرّ، مرّ، حلّ، ضمّ، سرّ
     * => مدد، شدد، ردد، عدد، فرر، مرر، حلل، ضمم، سرر
     *
     * @return list<RootCandidate>
     */
    private function extractF3ll(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ّ$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[2];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.96,
                source: 'rule:verb_triliteral_f3ll',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:فعلّ',
                    'pattern:triliteral_f3ll',
                    'rule:expand_final_shadda_to_repeated_radical',
                    'morphology:doubled_triliteral_verb',
                ],
            ),
        ];
    }

    /**
     * يفعلّ:
     * يمدّ، يشدّ، يردّ، يفرّ، يمرّ
     * => مدد، شدد، ردد، فرر، مرر
     *
     * @return list<RootCandidate>
     */
    private function extractYf3ll(string $word): array
    {
        if (preg_match('/^[يتأن]([\p{Arabic}])([\p{Arabic}])ّ$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[2];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.95,
                source: 'rule:verb_triliteral_yf3ll',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:يفعلّ',
                    'pattern:triliteral_yf3ll',
                    'rule:remove_present_prefix',
                    'rule:expand_final_shadda_to_repeated_radical',
                    'morphology:doubled_triliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * فعّل:
     * كبّر، صغّر، علّم، حسّن => كبر، صغر، علم، حسن
     *
     * @return list<RootCandidate>
     */
    private function extractF33l(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ّ([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.96,
                source: 'rule:verb_triliteral_f33l',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:فعّل',
                    'pattern:triliteral_f33l',
                    'rule:remove_shadda_from_second_radical',
                    'morphology:augmented_triliteral_verb',
                ],
            ),
        ];
    }

    /**
     * يفعّل:
     * يكبّر， يصغّر， يعلّم， يحسّن => كبر， صغر， علم， حسن
     *
     * @return list<RootCandidate>
     */
    private function extractYf33l(string $word): array
    {
        if (preg_match('/^[يتأن]([\p{Arabic}])([\p{Arabic}])ّ([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_triliteral_yf33l',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:يفعّل',
                    'pattern:triliteral_yf33l',
                    'rule:remove_present_prefix',
                    'rule:remove_shadda_from_second_radical',
                    'morphology:augmented_triliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * يفاعل:
     * يقاتل， يشارك， يباعد => قتل， شرك， بعد
     *
     * @return list<RootCandidate>
     */
    private function extractYfa3l(string $word): array
    {
        if (preg_match('/^[يتأن]([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.94,
                source: 'rule:verb_triliteral_yfa3l',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:يفاعل',
                    'pattern:triliteral_yfa3l',
                    'rule:remove_present_prefix',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:augmented_triliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * يكتب، تكتب، نكتب، اكتب، أكتب => كتب
     *
     * @return list<RootCandidate>
     */
    private function extractYf3l(string $word): array
    {
        if (preg_match('/^[يتأناأ]([\p{Arabic}]{3})$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.94,
                source: 'rule:verb_triliteral_yf3l',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:يفعل',
                    'pattern:triliteral_yf3l',
                    'rule:remove_present_prefix',
                    'morphology:present_triliteral_verb',
                ],
            ),
        ];
    }

    /**
     * كتب، فتح، كسر
     *
     * @return list<RootCandidate>
     */
    private function extractF3l(string $word): array
    {
        if (! $this->isStrongTriliteralRoot($word)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $word,
                confidence: 0.84,
                source: 'rule:verb_triliteral_f3l',
                reasons: [
                    'rule_family:verb',
                    'class:ثلاثي',
                    'form:فعل',
                    'pattern:triliteral_f3l',
                    'rule:bare_triliteral',
                    'caution:ambiguous_without_context',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
