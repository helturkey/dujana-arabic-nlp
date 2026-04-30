<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class MasdarNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            // Triliteral masdars
            array_push($roots, ...$this->extractF3ala($word));
            array_push($roots, ...$this->extractTf3eel($word));
            array_push($roots, ...$this->extractIf3al(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractIfaalaWeak(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));

            array_push($roots, ...$this->extractMfaa3la($word));

            // Quadriliteral masdars
            array_push($roots, ...$this->extractQuadriliteralF3lla($word));
            array_push($roots, ...$this->extractQuadriliteralF3lal($word));

            // Quinqueliteral masdars
            array_push($roots, ...$this->extractQuinqueliteralInf3al($word));
            array_push($roots, ...$this->extractQuinqueliteralIft3al($word));
            array_push($roots, ...$this->extractQuinqueliteralIf3lal($word));

            // Sextiliteral masdars
            array_push($roots, ...$this->extractSextiliteralIstf3al($word));
        }

        /*
         * تفعُّل needs originalSurface as haraka evidence.
         * The core/normalized form alone cannot distinguish تعلُّم from تعلّم.
         */
        array_push($roots, ...$this->extractTf33ul(
            word: $candidate->core,
            originalSurface: $candidate->originalSurface,
        ));

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * إفالة / إفعالة weak أفعل masdars:
     * إقامة، إجابة، إفادة، إطالة، إرادة...
     *
     * This is weak-aware and intentionally non-authoritative because weak-root
     * restoration can differ:
     * - إقامة => قوم / قيم
     * - إجابة => جوب / جيب
     * - إفادة => فود / فيد
     *
     * @return list<RootCandidate>
     */
    private function extractIfaalaWeak(string $word, string $originalSurface): array
    {
        if ($this->looksLikeAfalBrokenPlural($originalSurface)) {
            return [];
        }

        // Skip position 0 initial hamza/alif but guard the rest of the word.
        if ($this->containsHamza(mb_substr($word, 1))) {
            return [];
        }

        /*
         * Pattern: إ + R1 + ا + R2 + ة
         *
         * Covers:
         * إقامة، إجابة، إفادة، إطالة، إرادة
         */
        if (preg_match('/^[اإأ]([\p{Arabic}])ا([\p{Arabic}])ة$/u', $word, $m) !== 1) {
            return [];
        }

        /*
         * Guard against weak-initial / hamzated roots.
         *
         * Do not process things like:
         * إمارة، إشارة-like doubtful cases, or hamza-root cases here.
         *
         * No helper added to RootRuleHelpers; keep this local.
         */
        if (preg_match('/[وياىأإآؤئء]/u', $m[1]) === 1) {
            return [];
        }

        $candidates = [];

        $wawRoot = $m[1].'و'.$m[2];

        if ($this->isWeakAwareTriliteralRoot($wawRoot)) {
            $candidates[] = new RootCandidate(
                root: $wawRoot,
                confidence: 0.72,
                source: 'rule:masdar_triliteral_ifaala_weak',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي_أجوف',
                    'form:إفالة',
                    'pattern:ifaala_weak_waw',
                    'rule:remove_initial_hamza_or_alif',
                    'rule:restore_weak_middle_waw',
                    'rule:remove_ta_marbuta',
                    'morphology:form_iv_masdar_ajwaf_wawi',
                    'caution:weak_middle_radical_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            );
        }

        $yaRoot = $m[1].'ي'.$m[2];

        if ($this->isWeakAwareTriliteralRoot($yaRoot)) {
            $candidates[] = new RootCandidate(
                root: $yaRoot,
                confidence: 0.55,
                source: 'rule:masdar_triliteral_ifaala_weak',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي_أجوف',
                    'form:إفالة',
                    'pattern:ifaala_weak_ya',
                    'rule:remove_initial_hamza_or_alif',
                    'rule:restore_weak_middle_ya',
                    'rule:remove_ta_marbuta',
                    'morphology:form_iv_masdar_ajwaf_yai',
                    'caution:weak_middle_radical_restoration_is_ambiguous',
                    'not_authoritative_root',
                ],
            );
        }

        return $candidates;
    }

    /**
     * فعللة:
     * دحرجة، زلزلة، وسوسة => دحرج، زلزل، وسوس
     *
     * @return list<RootCandidate>
     */
    private function extractQuadriliteralF3lla(string $word): array
    {
        if (preg_match('/^([\p{Arabic}]{4})ة$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1];
        $isReduplicated = $this->looksLikeReduplicatedQuadriliteral($root);

        if (! $isReduplicated && ! $this->isBareQuadriliteralRoot($root)) {
            return [];
        }

        if (! $isReduplicated && $this->looksLikeNisbaSurface($root)) {
            return [];
        }

        if (! $isReduplicated && str_starts_with($root, 'م')) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: $isReduplicated ? 0.96 : 0.95,
                source: 'rule:masdar_quadriliteral_f3lla',
                reasons: array_values(array_filter([
                    'rule_family:masdar',
                    'class:رباعي',
                    'form:فعللة',
                    'pattern:f3lla',
                    'rule:remove_ta_marbuta',
                    $isReduplicated ? 'pattern:reduplicated_quadriliteral' : null,
                    'morphology:masdar_quadriliteral_noun',
                ])),
            ),
        ];
    }

    /**
     * فعلال:
     * زلزال => زلزل
     *
     * @return list<RootCandidate>
     */
    private function extractQuadriliteralF3lal(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        if ($m[1] !== $m[3] || $m[2] !== $m[4]) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1].$m[2].$m[3].$m[4],
                confidence: 0.93,
                source: 'rule:masdar_quadriliteral_f3lal',
                reasons: [
                    'rule_family:masdar',
                    'class:رباعي',
                    'form:فعلال',
                    'pattern:f3lal_reduplicated',
                    'rule:restore_reduplicated_quadriliteral_root',
                    'morphology:masdar_quadriliteral_noun',
                ],
            ),
        ];
    }

    /**
     * افتعال:
     * اجتماع، اقتراب، اختلاف
     *
     * @return list<RootCandidate>
     */
    private function extractQuinqueliteralIft3al(string $word): array
    {
        if (preg_match('/^ا([\p{Arabic}])ت([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1].$m[2].$m[3],
                confidence: 0.92,
                source: 'rule:masdar_quinqueliteral_ift3al',
                reasons: [
                    'rule_family:masdar',
                    'class:خماسي',
                    'form:افتعال',
                    'pattern:ift3al',
                    'rule:remove_initial_alif',
                    'rule:remove_infix_ta_after_first_radical',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:masdar_quinqueliteral_noun',
                ],
            ),
        ];
    }

    /**
     * انفعال:
     * انكسار، انفتاح، انقطاع => كسر، فتح، قطع
     *
     * @return list<RootCandidate>
     */
    private function extractQuinqueliteralInf3al(string $word): array
    {
        if (preg_match('/^ان([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1].$m[2].$m[3],
                confidence: 0.93,
                source: 'rule:masdar_quinqueliteral_inf3al',
                reasons: [
                    'rule_family:masdar',
                    'class:خماسي',
                    'form:انفعال',
                    'pattern:inf3al',
                    'rule:remove_prefix_ان',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:masdar_quinqueliteral_noun',
                ],
            ),
        ];
    }

    /**
     * مفاعلة:
     * مقاتلة، مشاركة، مباعدة => قتل، شرك، بعد
     *
     * @return list<RootCandidate>
     */
    private function extractMfaa3la(string $word): array
    {
        if (preg_match('/^م([\p{Arabic}])ا([\p{Arabic}])([\p{Arabic}])ة$/u', $word, $m) !== 1) {
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
                source: 'rule:masdar_triliteral_mfaa3la',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي',
                    'form:مفاعلة',
                    'pattern:mfaa3la',
                    'rule:remove_prefix_م',
                    'rule:remove_pattern_alif_after_first_radical',
                    'rule:remove_ta_marbuta',
                    'morphology:masdar_triliteral_noun',
                ],
            ),
        ];
    }

    /**
     * فِعالة / كِتابة:
     * كتابة => كتب
     * قراءة => قرأ needs hamza-aware rule later, so keep this conservative.
     *
     * @return list<RootCandidate>
     */
    private function extractF3ala(string $word): array
    {
        if (preg_match('/^([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])ة$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isStrongTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.88,
                source: 'rule:masdar_triliteral_f3ala',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي',
                    'form:فعالة',
                    'pattern:f3ala',
                    'rule:remove_pattern_alif_after_second_radical',
                    'rule:remove_ta_marbuta',
                    'morphology:masdar_triliteral_noun',
                    'caution:ambiguous_masdar_pattern',
                ],
            ),
        ];
    }

    /**
     * افعلال:
     * احمرار، اخضرار، اصفرار
     *
     * @return list<RootCandidate>
     */
    private function extractQuinqueliteralIf3lal(string $word): array
    {
        if (preg_match('/^ا([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        if ($m[3] !== $m[4]) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1].$m[2].$m[3],
                confidence: 0.93,
                source: 'rule:masdar_quinqueliteral_if3lal',
                reasons: [
                    'rule_family:masdar',
                    'class:خماسي',
                    'form:افعلال',
                    'pattern:if3lal',
                    'rule:remove_initial_alif',
                    'rule:remove_pattern_alif_before_final_radical',
                    'rule:collapse_final_repeated_radical',
                    'morphology:masdar_quinqueliteral_noun',
                ],
            ),
        ];
    }

    /**
     * إفعال:
     * إكرام، إخراج، إدخال، إحسان، إعلان => كرم، خرج، دخل، حسن، علن
     *
     * Conservative rule:
     * - Prefer explicit إ / ا forms.
     * - Do not treat أفعال-looking broken plurals like أقلام as authoritative masdars.
     *
     * @return list<RootCandidate>
     */
    private function extractIf3al(string $word, string $originalSurface): array
    {
        if ($this->looksLikeAfalBrokenPlural($originalSurface)) {
            return [];
        }

        if (preg_match('/^[اإأ]([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:masdar_triliteral_if3al',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي',
                    'form:إفعال',
                    'pattern:if3al',
                    'rule:remove_initial_hamza_or_alif',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:masdar_triliteral_noun',
                ],
            ),
        ];
    }

    /**
     * تفعيل:
     * تعليم => علم
     * تدريس => درس
     * تكبير => كبر
     *
     * @return list<RootCandidate>
     */
    private function extractTf3eel(string $word): array
    {
        if (preg_match('/^ت([\p{Arabic}])([\p{Arabic}])ي([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:masdar_triliteral_tf3eel',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي',
                    'form:تفعيل',
                    'pattern:tf3eel',
                    'rule:remove_prefix_ت',
                    'rule:remove_pattern_ya_after_second_radical',
                    'morphology:masdar_triliteral_noun',
                ],
            ),
        ];
    }

    /**
     * تفعُّل:
     * تعلُّم، تكبُّر، تحسُّن => علم، كبر، حسن
     *
     * @return list<RootCandidate>
     */
    private function extractTf33ul(string $word, string $originalSurface): array
    {
        if (! $this->hasNonShaddaHaraka($originalSurface)) {
            return [];
        }

        $word = $this->stripHarakatKeepShadda($word);

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
                confidence: 0.93,
                source: 'rule:masdar_triliteral_tf33ul',
                reasons: [
                    'rule_family:masdar',
                    'class:ثلاثي',
                    'form:تفعُّل',
                    'pattern:tf33ul',
                    'rule:requires_visible_haraka_evidence',
                    'rule:remove_prefix_ت',
                    'rule:remove_shadda_from_second_radical',
                    'morphology:masdar_triliteral_noun',
                    'caution:also_matches_tf3lla_past_surface_when_unvocalized',
                ],
            ),
        ];
    }

    /**
     * استفعال:
     * استخراج، استعمال، استقبال => خرج، عمل، قبل
     *
     * @return list<RootCandidate>
     */
    private function extractSextiliteralIstf3al(string $word): array
    {
        if (preg_match('/^است([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1].$m[2].$m[3],
                confidence: 0.94,
                source: 'rule:masdar_sextiliteral_istf3al',
                reasons: [
                    'rule_family:masdar',
                    'class:سداسي',
                    'form:استفعال',
                    'pattern:istf3al',
                    'rule:remove_prefix_است',
                    'rule:remove_pattern_alif_after_second_radical',
                    'morphology:masdar_sextiliteral_noun',
                ],
            ),
        ];
    }
}
