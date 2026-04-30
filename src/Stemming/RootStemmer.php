<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Stemming;

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Morphology\Root\RootAnalysis;
use Dujana\ArabicNlp\Morphology\Root\RootExtractor;

final class RootStemmer
{
    private ?RootAnalysis $lastAnalysis = null;

    public function __construct(
        private RootExtractor $rootExtractor,
    ) {}

    public static function make(?LexiconLookup $lookup = null): self
    {
        return new self(RootExtractor::make($lookup));
    }

    public function analyze(CoreCandidate $candidate): RootAnalysis
    {
        return $this->rootExtractor->extract($candidate);
    }

    public function stem(CoreCandidate $candidate): string
    {
        $this->lastAnalysis = $this->rootExtractor->extract($candidate);

        return $this->lastAnalysis->rootOr($candidate->core);
    }

    public function lastAnalysis(): ?RootAnalysis
    {
        return $this->lastAnalysis;
    }
}
