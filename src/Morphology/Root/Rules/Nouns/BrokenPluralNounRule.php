<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class BrokenPluralNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        return $this->uniqueRootCandidates([
            ...$this->extractAf3aal($candidate->originalSurface),

            ...$this->extractF3aal(
                originalSurface: $candidate->originalSurface,
                firstHaraka: 'ِ',
                source: 'rule:broken_plural_fi3aal',
                form: 'فِعال',
                pattern: 'fi3aal',
                confidence: 0.52,
                harakaReason: 'rule:requires_visible_kasra_evidence',
            ),

            ...$this->extractF3aal(
                originalSurface: $candidate->originalSurface,
                firstHaraka: 'ُ',
                source: 'rule:broken_plural_fu3aal',
                form: 'فُعال',
                pattern: 'fu3aal',
                confidence: 0.50,
                harakaReason: 'rule:requires_visible_damma_evidence',
            ),

            ...$this->extractMfaa3il($candidate->originalSurface),
            ...$this->extractMfaa3eel($candidate->originalSurface),
            ...$this->extractFa3aa2il($candidate->originalSurface),
            ...$this->extractFu3alaa2($candidate->originalSurface),
        ]);
    }

    /**
     * أفعال:
     * أقلام => قلم
     * ألوان => لون
     * أشعار => شعر
     *
     * @return list<RootCandidate>
     */
    private function extractAf3aal(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if ($this->containsHamza(mb_substr($surface, 1))) {
            return [];
        }

        if (preg_match('/^[اأإآ]([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.50,
                source: 'rule:broken_plural_af3aal',
                reasons: [
                    'rule_family:broken_plural',
                    'form:أفعال',
                    'pattern:af3aal',
                    'rule:remove_initial_hamza_or_alif',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:broken_plural_noun',
                    'caution:broken_plural_root_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فِعال / فُعال:
     * جِبال، رِجال، كِلاب => جبل، رجل، كلب
     * رُكاب => ركب
     *
     * Requires visible haraka evidence because unvocalized فعال is broad.
     *
     * @return list<RootCandidate>
     */
    private function extractF3aal(
        string $originalSurface,
        string $firstHaraka,
        string $source,
        string $form,
        string $pattern,
        float $confidence,
        string $harakaReason,
    ): array {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        $quotedHaraka = preg_quote($firstHaraka, '/');

        if (preg_match('/^([\p{Arabic}])'.$quotedHaraka.'([\p{Arabic}])ا([\p{Arabic}])$/u', $originalSurface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: $confidence,
                source: $source,
                reasons: [
                    'rule_family:broken_plural',
                    'form:'.$form,
                    'pattern:'.$pattern,
                    $harakaReason,
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:broken_plural_noun',
                    'caution:broken_plural_root_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * مفاعل:
     * مساجد، مكاتب، مدارس => سجد، كتب، درس
     *
     * @return list<RootCandidate>
     */
    private function extractMfaa3il(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if ($this->containsHamza($surface)) {
            return [];
        }

        if (preg_match('/^م([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.48,
                source: 'rule:broken_plural_mfaa3il',
                reasons: [
                    'rule_family:broken_plural',
                    'form:مفاعل',
                    'pattern:mfaa3il',
                    'rule:remove_prefix_م',
                    'rule:remove_pattern_alif_after_first_radical',
                    'morphology:broken_plural_noun',
                    'caution:plural_of_derived_noun_not_always_direct_root',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * مفاعيل:
     * مفاتيح، مصابيح => فتح، صبح
     *
     * @return list<RootCandidate>
     */
    private function extractMfaa3eel(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if ($this->containsHamza($surface)) {
            return [];
        }

        if (preg_match('/^م([\p{Arabic}])ا([\p{Arabic}])ي([\p{Arabic}])$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.47,
                source: 'rule:broken_plural_mfaa3eel',
                reasons: [
                    'rule_family:broken_plural',
                    'form:مفاعيل',
                    'pattern:mfaa3eel',
                    'rule:remove_prefix_م',
                    'rule:remove_pattern_alif_after_first_radical',
                    'rule:remove_pattern_ya_after_second_radical',
                    'morphology:broken_plural_noun',
                    'caution:plural_of_derived_noun_not_always_direct_root',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فعائل:
     * قبائل، رسائل، عجائب، غرائب، صحائف => قبل، رسل، عجب، غرب، صحف
     *
     * @return list<RootCandidate>
     */
    private function extractFa3aa2il(string $originalSurface): array
    {
        $surface = $this->surfaceShape($originalSurface);

        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ا[ئء]([\p{Arabic}])$/u', $surface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.46,
                source: 'rule:broken_plural_fa3aa2il',
                reasons: [
                    'rule_family:broken_plural',
                    'form:فعائل',
                    'pattern:fa3aa2il',
                    'rule:remove_pattern_alif_hamza_after_second_radical',
                    'morphology:broken_plural_noun',
                    'caution:broken_plural_root_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فُعلاء:
     * عُلَماء، فُقَراء، كُرَماء، شُعَراء => علم، فقر، كرم، شعر
     *
     * Requires visible damma evidence because unvocalized فعلاء overlaps with
     * other feminine/color patterns.
     *
     * @return list<RootCandidate>
     */
    private function extractFu3alaa2(string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        if (preg_match('/^([\p{Arabic}])ُ([\p{Arabic}])َ?([\p{Arabic}])اء$/u', $originalSurface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.48,
                source: 'rule:broken_plural_fu3alaa2',
                reasons: [
                    'rule_family:broken_plural',
                    'form:فُعلاء',
                    'pattern:fu3alaa2',
                    'rule:requires_visible_damma_evidence',
                    'rule:remove_final_alif_hamza',
                    'morphology:broken_plural_noun',
                    'caution:broken_plural_root_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
