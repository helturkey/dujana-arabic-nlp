<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Verbs;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class QuinqueliteralVerbPatternRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractInf3la(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractYnf3l(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            /*
             * Must run before regular افتعل:
             * اتصل => وصل, not تصل
             * يصطبر => صبر, not صطبر
             */
            array_push($roots, ...$this->extractIft3laAssimilated($word));
            array_push($roots, ...$this->extractYft3lAssimilated($word));

            array_push($roots, ...$this->extractIft3la($word));
            array_push($roots, ...$this->extractYft3l($word));

            array_push($roots, ...$this->extractTf33la($word));
            array_push($roots, ...$this->extractYtf33l($word));

            array_push($roots, ...$this->extractTfa3la($word));
            array_push($roots, ...$this->extractYtfa3l($word));

            array_push($roots, ...$this->extractIf3ll($word));
            array_push($roots, ...$this->extractYf3ll($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * افتعل - assimilated:
     * اصطبر => صبر
     * اضطرب => ضرب
     * ازدحم => زحم
     * اتصل / تصل => وصل
     * اتصف / تصف => وصف
     *
     * @return list<RootCandidate>
     */
    private function extractIft3laAssimilated(string $word): array
    {
        if ($this->containsHamza($word)) {
            return [];
        }

        $root = null;
        $assimilation = null;

        if (preg_match('/^اصط([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'ص'.$m[1].$m[2];
            $assimilation = 'ift3la_ta_assimilated_to_taa_after_sad';
        } elseif (preg_match('/^اضط([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'ض'.$m[1].$m[2];
            $assimilation = 'ift3la_ta_assimilated_to_taa_after_dad';
        } elseif (preg_match('/^از(?:د)?([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'ز'.$m[1].$m[2];
            $assimilation = 'ift3la_ta_assimilated_to_dal_or_zay_cluster';
        } elseif (preg_match('/^(?:ا)?تص([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'وص'.$m[1];
            $assimilation = 'ift3la_initial_waw_assimilated_in_ittasala_like_form';
        }

        if ($root === null) {
            return [];
        }

        if (! $this->isAssimilatedIftaalaRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.97,
                source: 'rule:verb_ift3la_assimilated',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:افتعل',
                    'pattern:ift3la_assimilated',
                    'rule:restore_ta_or_initial_weak_radical',
                    'assimilation:'.$assimilation,
                    'morphology:quinqueliteral_derived_verb',
                    'caution:assimilated_ift3la_pattern',
                ],
            ),
        ];
    }

    /**
     * يفتعل - assimilated:
     * يصطبر / صطبر => صبر
     * يضطرب / ضطرب => ضرب
     * يزدحم / زدحم => زحم
     * يتصل / تصل => وصل
     * يتصف / تصف => وصف
     *
     * @return list<RootCandidate>
     */
    private function extractYft3lAssimilated(string $word): array
    {
        if ($this->containsHamza($word)) {
            return [];
        }

        $root = null;
        $assimilation = null;

        if (preg_match('/^(?:[يتأن])?صط([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'ص'.$m[1].$m[2];
            $assimilation = 'yift3l_ta_assimilated_to_taa_after_sad';
        } elseif (preg_match('/^(?:[يتأن])?ضط([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'ض'.$m[1].$m[2];
            $assimilation = 'yift3l_ta_assimilated_to_taa_after_dad';
        } elseif (preg_match('/^(?:[يتأن])?ز(?:د)?([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'ز'.$m[1].$m[2];
            $assimilation = 'yift3l_ta_assimilated_to_dal_or_zay_cluster';
        } elseif (preg_match('/^(?:[يتأن])?تص([\p{Arabic}])$/u', $word, $m) === 1) {
            $root = 'وص'.$m[1];
            $assimilation = 'yift3l_initial_waw_assimilated_in_yattasila_like_form';
        }

        if ($root === null) {
            return [];
        }

        if (! $this->isAssimilatedIftaalaRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.96,
                source: 'rule:verb_yift3l_assimilated',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:يفتعل',
                    'pattern:yift3l_assimilated',
                    'rule:remove_present_prefix',
                    'rule:restore_assimilated_ta_or_initial_weak_radical',
                    'assimilation:'.$assimilation,
                    'morphology:quinqueliteral_present_verb',
                    'caution:assimilated_yift3l_pattern',
                ],
            ),
        ];
    }

    /**
     * انفعل:
     * انكسر، انفتح، انقطع => كسر، فتح، قطع
     *
     * @return list<RootCandidate>
     */
    private function extractInf3la(string $word, string $originalSurface): array
    {
        if ($this->looksLikeNisbaSurface($originalSurface)) {
            return [];
        }

        if (mb_strlen($word) !== 5) {
            return [];
        }

        if (preg_match('/^ان([\p{Arabic}]{3})$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.95,
                source: 'rule:verb_inf3la',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:انفعل',
                    'pattern:inf3la',
                    'rule:remove_prefix_ان',
                    'morphology:quinqueliteral_derived_verb',
                ],
            ),
        ];
    }

    /**
     * ينفعل:
     * ينكسر، تنكسر، ننكسر => كسر، كسر، كسر
     *
     * @return list<RootCandidate>
     */
    private function extractYnf3l(string $word, string $originalSurface): array
    {
        if ($this->looksLikeNisbaSurface($originalSurface)) {
            return [];
        }

        if (mb_strlen($word) !== 6) {
            return [];
        }

        if (preg_match('/^[يتأن]ن([\p{Arabic}]{3})$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_ynf3l',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:ينفعل',
                    'pattern:ynf3l',
                    'rule:remove_present_prefix',
                    'rule:remove_inf3la_nun',
                    'morphology:quinqueliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * افتعل:
     * اجتمع، اقترب، اختلف => جمع، قرب، خلف
     *
     * @return list<RootCandidate>
     */
    private function extractIft3la(string $word): array
    {
        if (preg_match('/^ا([\p{Arabic}])ت([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_ift3la',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:افتعل',
                    'pattern:ift3la',
                    'rule:remove_initial_alif',
                    'rule:remove_infix_ta_after_first_radical',
                    'morphology:quinqueliteral_derived_verb',
                ],
            ),
        ];
    }

    /**
     * يفتعل:
     * يجتمع، يقترب، يختلف => جمع، قرب، خلف
     *
     * @return list<RootCandidate>
     */
    private function extractYft3l(string $word): array
    {
        if (preg_match('/^[يتأن]([\p{Arabic}])ت([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_yft3l',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:يفتعل',
                    'pattern:yft3l',
                    'rule:remove_present_prefix',
                    'rule:remove_infix_ta_after_first_radical',
                    'morphology:quinqueliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * تفعّل:
     * تعلّم، تكبّر، تحسّن => علم، كبر، حسن
     *
     * @return list<RootCandidate>
     */
    private function extractTf33la(string $word): array
    {
        if (preg_match('/^ت([\p{Arabic}])([\p{Arabic}])ّ([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_tf33la',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:تفعّل',
                    'pattern:tf33la',
                    'rule:remove_prefix_ت',
                    'rule:remove_shadda_from_second_radical',
                    'morphology:quinqueliteral_derived_verb',
                ],
            ),
        ];
    }

    /**
     * يتفعّل:
     * يتعلّم، يتكبّر، يتحسّن => علم، كبر، حسن
     *
     * @return list<RootCandidate>
     */
    private function extractYtf33l(string $word): array
    {
        if (preg_match('/^[يتأن]ت([\p{Arabic}])([\p{Arabic}])ّ([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_ytf33l',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:يتفعّل',
                    'pattern:ytf33l',
                    'rule:remove_present_prefix',
                    'rule:remove_prefix_ت',
                    'rule:remove_shadda_from_second_radical',
                    'morphology:quinqueliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * تفاعل:
     * تقاتل، تشارك، تباعد => قتل، شرك، بعد
     *
     * @return list<RootCandidate>
     */
    private function extractTfa3la(string $word): array
    {
        if ($this->containsHamza($word)) {
            return [];
        }

        if (preg_match('/^ت([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_tfa3la',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:تفاعل',
                    'pattern:tfa3la',
                    'rule:remove_prefix_ت',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:quinqueliteral_derived_verb',
                ],
            ),
        ];
    }

    /**
     * يتفاعل:
     * يتقاتل، يتشارك، يتباعد => قتل، شرك، بعد
     *
     * @return list<RootCandidate>
     */
    private function extractYtfa3l(string $word): array
    {
        if ($this->containsHamza($word)) {
            return [];
        }

        if (preg_match('/^[يتأن]ت([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_ytfa3l',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:يتفاعل',
                    'pattern:ytfa3l',
                    'rule:remove_present_prefix',
                    'rule:remove_prefix_ت',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:quinqueliteral_present_verb',
                ],
            ),
        ];
    }

    /**
     * افعلّ:
     * احمرّ، اخضرّ، اصفرّ => حمر، خضر، صفر
     *
     * @return list<RootCandidate>
     */
    private function extractIf3ll(string $word): array
    {
        if (preg_match('/^ا([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ّ$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_if3ll',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:افعلّ',
                    'pattern:if3ll',
                    'rule:remove_initial_alif',
                    'rule:remove_final_shadda',
                    'morphology:color_or_defect_verb',
                ],
            ),
        ];
    }

    /**
     * يفعلّ:
     * يحمرّ، يخضرّ، يصفرّ => حمر، خضر، صفر
     *
     * @return list<RootCandidate>
     */
    private function extractYf3ll(string $word): array
    {
        if (preg_match('/^[يتأن]([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ّ$/u', $word, $m) !== 1) {
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
                source: 'rule:verb_yf3ll',
                reasons: [
                    'rule_family:verb',
                    'class:خماسي',
                    'form:يفعلّ',
                    'pattern:yf3ll',
                    'rule:remove_present_prefix',
                    'rule:remove_final_shadda',
                    'morphology:color_or_defect_present_verb',
                ],
            ),
        ];
    }
}
