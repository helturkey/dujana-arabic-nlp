<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Morphology\Root\Rules;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Contracts\Morphology\MorphologicalRuleContract;
use Dujana\ArabicNlp\Contracts\Morphology\RootExtractorContract;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;

final readonly class RuleBasedRootExtractor implements RootExtractorContract
{
    /**
     * @param  list<MorphologicalRuleContract>  $rules
     */
    public function __construct(
        private array $rules,
    ) {}

    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array
    {
        $roots = [];

        foreach ($this->rules as $rule) {
            array_push($roots, ...$rule->extract($candidate));
        }

        return $roots;
    }
}
