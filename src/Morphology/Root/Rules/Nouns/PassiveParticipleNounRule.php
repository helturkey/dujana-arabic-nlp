<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Nouns;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class PassiveParticipleNounRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractMf33l($word));
            array_push($roots, ...$this->extractMf3ol($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * مفعّل:
     * معلّم => علم
     * محسّن => حسن
     * مكرّم => كرم
     * مدرّس => درس
     *
     * Requires visible shadda preserved by morphology normalization.
     *
     * @return list<RootCandidate>
     */
    private function extractMf33l(string $word): array
    {
        if (preg_match('/^م([\p{Arabic}])([\p{Arabic}])ّ([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:passive_participle_mf33l',
                reasons: [
                    'rule_family:passive_participle',
                    'form:مفعّل',
                    'pattern:mf33l',
                    'rule:remove_prefix_م',
                    'rule:remove_shadda_from_second_radical',
                    'morphology:passive_participle_noun',
                ],
            ),
        ];
    }

    /**
     * مفعول:
     * مكتوب => كتب
     * معلوم => علم
     * مفهوم => فهم
     *
     * Conservative: strong triliteral only.
     *
     * @return list<RootCandidate>
     */
    private function extractMf3ol(string $word): array
    {
        if (preg_match('/^م([\p{Arabic}])([\p{Arabic}])و([\p{Arabic}])$/u', $word, $m) !== 1) {
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
                source: 'rule:passive_participle_mf3ol',
                reasons: [
                    'rule_family:passive_participle',
                    'form:مفعول',
                    'pattern:mf3ol',
                    'rule:remove_prefix_م',
                    'rule:remove_pattern_waw_after_second_radical',
                    'morphology:passive_participle_noun',
                    'caution:ambiguous_passive_participle_pattern',
                ],
            ),
        ];
    }
}
