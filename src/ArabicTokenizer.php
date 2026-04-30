<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp;

use Dujana\ArabicNlp\Text\ArabicTokenizer as BaseArabicTokenizer;

final readonly class ArabicTokenizer
{
    public function __construct(
        private BaseArabicTokenizer $tokenizer = new BaseArabicTokenizer,
    ) {}

    public static function make(): self
    {
        return new self;
    }

    /**
     * @return list<string>
     */
    public function tokenize(string $text): array
    {
        return $this->tokenizer->tokenize($text);
    }

    /**
     * Alias for tokenize().
     *
     * @return list<string>
     */
    public function tokens(string $text): array
    {
        return $this->tokenize($text);
    }
}
