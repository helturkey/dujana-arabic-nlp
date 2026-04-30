<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class PlaceTimeNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractMf3l($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * مَفْعَل / مَفْعِل:
     * مكتب => كتب
     * ملعب => لعب
     * مخرج => خرج
     * مدخل => دخل
     * مجلس => جلس
     * مورد => ورد
     *
     * In mostly unvocalized Arabic, مفعل and مفعل overlap on the same surface,
     * so this rule exposes one conservative diagnostic candidate.
     *
     * @return list<RootCandidate>
     */
    private function extractMf3l(string $word): array
    {
        if (
            $this->hasDerivedMeemPrefix($word)
            || $this->looksLikeNisbaSurface($word)
            || $this->looksLikeFuulPluralAfterMeem($word)
        ) {
            return [];
        }

        if (preg_match('/^م([\p{Arabic}])([\p{Arabic}])([\p{Arabic}])$/u', $word, $m) !== 1) {
            return [];
        }

        $root = $m[1].$m[2].$m[3];

        if (! $this->isWeakAwareTriliteralRoot($root)) {
            return [];
        }

        return [
            new RootCandidate(
                root: $root,
                confidence: 0.84,
                source: 'rule:place_time_mf3l',
                reasons: [
                    'rule_family:place_time',
                    'form:مفعل',
                    'pattern:mf3l',
                    'rule:remove_prefix_م',
                    'morphology:place_time_noun',
                    'caution:ambiguous_place_time_pattern',
                    'not_authoritative_root',
                ],
            ),
        ];
    }
}
