<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp;

use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

final class ArabicStemmer
{
    public function __construct(private readonly ArabicAnalyzer $analyzer) {}

    public static function make(?ArabicNlpConfig $config = null): self
    {
        return new self(ArabicAnalyzer::make($config));
    }

    public function stem(string $word, ?StemmerModeEnum $mode = null): string
    {
        return $this->analyzer->stem($word, $mode);
    }

    public function analyze(string $word, ?StemmerModeEnum $mode = null): ArabicAnalysis
    {
        return $this->analyzer->analyze($word, $mode);
    }

    /** @param list<string> $words @return list<string> */
    public function stemMultiple(array $words, ?StemmerModeEnum $mode = null): array
    {
        return $this->analyzer->stemMultiple($words, $mode);
    }

    /** @return list<string> */
    public function stemSentence(string $sentence, ?StemmerModeEnum $mode = null): array
    {
        return $this->analyzer->stemSentence($sentence, $mode);
    }

    public function stemText(string $text, ?StemmerModeEnum $mode = null): string
    {
        return $this->analyzer->stemText($text, $mode);
    }

    public function stemSentenceAsString(string $sentence, ?StemmerModeEnum $mode = null): string
    {
        return $this->analyzer->stemSentenceAsString($sentence, $mode);
    }
}
