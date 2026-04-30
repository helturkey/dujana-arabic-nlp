<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

final readonly class LexiconStatsResult
{
    /**
     * @param  array<string,int>  $totals
     * @param  list<array{source:string,count:int}>  $sources
     * @param  list<array{pos_cat:string,count:int}>  $posCategories
     * @param  list<array{language:string,count:int}>  $languages
     */
    public function __construct(
        public string $databasePath,
        public array $totals,
        public array $sources,
        public array $posCategories,
        public array $languages,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'database' => $this->databasePath,
            'totals' => $this->totals,
            'sources' => $this->sources,
            'pos_categories' => $this->posCategories,
            'languages' => $this->languages,
        ];
    }
}
