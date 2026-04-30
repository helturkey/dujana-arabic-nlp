<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

final readonly class LexiconBuildResult
{
    /**
     * @param  array<string,int>  $imported
     */
    public function __construct(
        public string $outputPath,
        public array $imported,
        public int $entries,
    ) {}
}
