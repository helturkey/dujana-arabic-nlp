<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Stemming;

use Dujana\ArabicNlp\Candidates\CoreCandidate;

final class LightStemmer
{
    public function stem(CoreCandidate $candidate): string
    {
        return $candidate->core;
    }
}
