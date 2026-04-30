<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

final readonly class RootEvaluationCase
{
    public function __construct(
        public string $word,
        public ?string $expectedRoot,
        public ?string $category = null,
        public ?string $note = null,
    ) {}

    /**
     * @param  array<int,string>  $columns
     */
    public static function fromColumns(array $columns): ?self
    {
        $word = trim($columns[0] ?? '');

        if ($word === '' || str_starts_with($word, '#')) {
            return null;
        }

        $expected = trim($columns[1] ?? '');
        $category = trim($columns[2] ?? '');
        $note = trim($columns[3] ?? '');

        return new self(
            word: $word,
            expectedRoot: $expected !== '' ? $expected : null,
            category: $category !== '' ? $category : null,
            note: $note !== '' ? $note : null,
        );
    }
}
