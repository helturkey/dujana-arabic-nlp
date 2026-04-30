<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class DerivedAdjectiveNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractAf3l($word));
            array_push($roots, ...$this->extractF3laa($word));

            array_push($roots, ...$this->extractF3lColorPlural(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractF33aal($word));
            array_push($roots, ...$this->extractF3ol($word));
            array_push($roots, ...$this->extractF3eel($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * فُعْل color/defect plural/adjective:
     * حُمْر، خُضْر، صُفْر، عُرْج => حمر، خضر، صفر، عرج
     *
     * Requires visible haraka evidence because the unvocalized surface is identical
     * to a bare triliteral root.
     *
     * @return list<RootCandidate>
     */
    private function extractF3lColorPlural(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        /*
         * Accept:
         * حُمْر، خُضْر، صُفْر، عُرْج
         *
         * Also accepts partially vocalized فُعل if sukun is missing.
         */
        if (preg_match('/^([\p{Arabic}])ُ([\p{Arabic}])ْ?([\p{Arabic}])$/u', $originalSurface, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.82,
                source: 'rule:adjective_f3l_color_plural',
                reasons: [
                    'rule_family:adjective',
                    'form:فُعْل',
                    'pattern:f3l',
                    'rule:requires_visible_damma_evidence',
                    'rule:surface_equals_root_after_harakat_removed',
                    'morphology:color_or_defect_plural_adjective',
                    'caution:surface_identical_to_bare_triliteral_root_when_unvocalized',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * أفعل:
     * أحمر، أخضر، أصفر، أكبر، أصغر، أفضل، أجمل => حمر، خضر، صفر، كبر، صغر، فضل، جمل
     *
     * Covers color/defect adjectives and comparative/superlative.
     *
     * @return list<RootCandidate>
     */
    private function extractAf3l(string $word): array
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
                confidence: 0.85,
                source: 'rule:adjective_af3l',
                reasons: [
                    'rule_family:adjective',
                    'form:أفعل',
                    'pattern:af3l',
                    'rule:remove_initial_hamza_or_alif',
                    'morphology:color_defect_or_comparative_adjective',
                    'caution:ambiguous_with_af3la_verb',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فعلاء:
     * حمراء، خضراء، صفراء، عرجاء => حمر، خضر، صفر، عرج
     *
     * Feminine counterpart of أفعل color/defect adjectives.
     *
     * @return list<RootCandidate>
     */
    private function extractF3laa(string $word): array
    {
        if ($this->containsHamza(mb_substr($word, 0, -1))) {
            return [];
        }

        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])اء$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.86,
                source: 'rule:adjective_f3laa',
                reasons: [
                    'rule_family:adjective',
                    'form:فعلاء',
                    'pattern:f3laa',
                    'rule:remove_final_hamza_alif',
                    'morphology:feminine_color_or_defect_adjective',
                    'caution:ambiguous_feminine_adjective_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فعّال:
     * غفّار، ضرّاب، قتّال، علّام => غفر، ضرب، قتل، علم
     *
     * Requires visible shadda.
     *
     * @return list<RootCandidate>
     */
    private function extractF33aal(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ّا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.90,
                source: 'rule:exaggeration_f33aal',
                reasons: [
                    'rule_family:exaggeration',
                    'form:فعّال',
                    'pattern:f33aal',
                    'rule:remove_shadda_from_second_radical',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:exaggeration_noun',
                    'caution:ambiguous_exaggeration_or_profession_pattern',
                ],
            ),
        ];
    }

    /**
     * فعول:
     * صبور، شكور، غفور، حسود => صبر، شكر، غفر، حسد
     *
     * @return list<RootCandidate>
     */
    private function extractF3ol(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])و([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.86,
                source: 'rule:exaggeration_f3ol',
                reasons: [
                    'rule_family:exaggeration',
                    'form:فعول',
                    'pattern:f3ol',
                    'rule:remove_pattern_waw_after_second_radical',
                    'morphology:exaggeration_or_adjective_noun',
                    'caution:ambiguous_exaggeration_or_adjective_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * فعيل:
     * كريم، عليم، رحيم، جميل، كبير، صغير => كرم، علم، رحم، جمل، كبر، صغر
     *
     * @return list<RootCandidate>
     */
    private function extractF3eel(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ي([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:adjective_f3eel',
                reasons: [
                    'rule_family:adjective',
                    'form:فعيل',
                    'pattern:f3eel',
                    'rule:remove_pattern_ya_after_second_radical',
                    'morphology:adjective_or_exaggeration_noun',
                    'caution:ambiguous_adjective_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
