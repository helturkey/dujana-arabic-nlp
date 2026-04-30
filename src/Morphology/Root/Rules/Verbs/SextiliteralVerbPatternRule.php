<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules\Verbs;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\Rules\Concerns\RootRuleHelpers;

final class SextiliteralVerbPatternRule implements MorphologicalRuleContract
{
    use RootRuleHelpers;

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->candidateWords($candidate) as $word) {
            array_push($roots, ...$this->extractIstf3la($word));
            array_push($roots, ...$this->extractYstf3l($word));
        }

        return $this->uniqueRootCandidates($roots);
    }

    /**
     * استفعل:
     * استخرج، استعمل، استقبل، استغفر
     *
     * @return list<RootCandidate>
     */
    private function extractIstf3la(string $word): array
    {
        if (preg_match('/^است([\p{Arabic}]{3})$/u', $word, $m) !== 1) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1],
                confidence: 0.96,
                source: 'rule:verb_istf3la',
                reasons: [
                    'rule_family:verb',
                    'class:سداسي',
                    'form:استفعل',
                    'pattern:istf3la',
                    'rule:remove_prefix_است',
                    'morphology:sextiliteral_derived_verb',
                ],
            ),
        ];
    }

    /**
     * يستفعل:
     * يستخرج، يستعمل، يستقبل، يستغفر
     *
     * @return list<RootCandidate>
     */
    private function extractYstf3l(string $word): array
    {
        if (preg_match('/^[يتأن]ست([\p{Arabic}]{3})$/u', $word, $m) !== 1) {
            return [];
        }

        return [
            new RootCandidate(
                root: $m[1],
                confidence: 0.95,
                source: 'rule:verb_ystf3l',
                reasons: [
                    'rule_family:verb',
                    'class:سداسي',
                    'form:يستفعل',
                    'pattern:ystf3l',
                    'rule:remove_present_prefix',
                    'rule:remove_prefix_ست',
                    'morphology:sextiliteral_present_verb',
                ],
            ),
        ];
    }
}
