<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Contracts\Morphology;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;

interface MorphologicalRuleContract
{
    /**
     * @return list<RootCandidate>
     */
    public function extract(CoreCandidate $candidate): array;
}
