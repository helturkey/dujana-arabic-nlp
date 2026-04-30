<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class InstrumentNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractMf3al($word));
            array_push($roots, ...$this->extractMf3l($word));
            array_push($roots, ...$this->extractMf3la(
                word: $word,
                originalSurface: $candidate->originalSurface,
            ));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * مِفعل / مِفعل-like instrument nouns:
     * مبرد، مشرط، منجل...
     *
     * Highly ambiguous in unvocalized Arabic because it overlaps with:
     * - place/time مفعل
     * - active participle مُفعل
     *
     * @return list<RootCandidate>
     */
    private function extractMf3l(string $word): array
    {
        $surface = $this->surfaceShape($word);

        if ($this->hasDerivedMeemPrefix($surface)) {
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
                confidence: 0.76,
                source: 'rule:instrument_mf3l',
                reasons: [
                    'rule_family:instrument',
                    'form:مفعل',
                    'pattern:mf3l',
                    'rule:remove_prefix_م',
                    'morphology:instrument_noun',
                    'caution:ambiguous_instrument_or_place_time_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }

    /**
     * مفعلة / مفعلة-like instrument nouns:
     * مكنسة، مطرقة، مبردة...
     *
     * @return list<RootCandidate>
     */
    private function extractMf3la(string $word, string $originalSurface): array
    {
        if ($this->looksLikeFeminineNisbaSurface($originalSurface)) {
            return [];
        }

        if (str_starts_with($word, 'مت') || str_starts_with($word, 'مست')) {
            return [];
        }

        // مكنسة، مطرقة، مبردة
        if (preg_match('/^م([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])ة$/u', $word, $m) === 1) {
            return $this->makeMf3laCandidate(
                root: $m[1].$m[2].$m[3],
                coreWithoutTaMarbuta: false,
            );
        }

        /*
         * مكنس، مطرق، مبرد — only when original had ta marbuta.
         *
         * Do NOT classify مكتب، ملعب، مخرج، مدخل، مجلس as instruments.
         */
        if (
            str_ends_with($originalSurface, 'ة')
            && preg_match('/^م([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) === 1
        ) {
            return $this->makeMf3laCandidate(
                root: $m[1].$m[2].$m[3],
                coreWithoutTaMarbuta: true,
            );
        }

        return [];
    }

    /**
     * مفعال / مفعال-like instrument nouns
     *
     * @return list<RootCandidate>
     */
    private function extractMf3al(string $word): array
    {
        if (str_starts_with($word, 'مت') || str_starts_with($word, 'مست')) {
            return [];
        }

        if (preg_match('/^م([\p{Arabic}])([\p{Arabic}])ا([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $surfaceRoot = $m[1].$m[2].$m[3];
        $root = $this->restoreInitialWeakRadicalForMf3al($surfaceRoot);

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        $reasons = [
            'rule_family:instrument',
            'form:مفعال',
            'pattern:mf3al',
            'rule:remove_prefix_م',
            'rule:remove_pattern_alif_after_second_radical',
            'morphology:instrument_noun',
            'caution:ambiguous_instrument_pattern',
        ];

        if ($root !== $surfaceRoot) {
            $reasons[] = 'rule:restore_initial_waw_from_surface_ya';
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.94,
                source: 'rule:instrument_mf3al',
                reasons: $reasons,
            ),
        ];
    }

    /**
     * @return list<RootCandidate>
     */
    private function makeMf3laCandidate(string $root, bool $coreWithoutTaMarbuta): array
    {
        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        $reasons = [
            'rule_family:instrument',
            'form:مفعلة',
            'pattern:mf3la',
            'rule:remove_prefix_م',
            'morphology:instrument_noun',
            'caution:ambiguous_instrument_or_place_pattern',
        ];

        $reasons[] = $coreWithoutTaMarbuta
            ? 'rule:core_without_ta_marbuta'
            : 'rule:remove_ta_marbuta';

        return [
            new RootCandidate(
                root: $root,
                confidence: $coreWithoutTaMarbuta ? 0.94 : 0.95,
                source: 'rule:instrument_mf3la',
                reasons: $reasons,
            ),
        ];
    }

    private function restoreInitialWeakRadicalForMf3al(string $surfaceRoot): string
    {
        /*
         * General weak-initial مفعال/ميعال handling:
         *
         * ميزان => surface يزن => root وزن
         * ميعاد => surface يعد => root وعد
         * ميقات => surface يقت => root وقت
         * ميراث => surface يرث => root ورث
         * ميلاد => surface يلد => root ولد
         *
         * This is intentionally broad. Known exceptions should be corrected by
         * database/manual-roots.tsv because DB/manual has higher priority than rules.
         */
        if (str_starts_with($surfaceRoot, 'ي')) {
            return 'و'.mb_substr($surfaceRoot, 1);
        }

        return $surfaceRoot;
    }
}
