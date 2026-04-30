<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;

final readonly class LexiconLookupResult
{
    /**
     * @param  list<string>  $lookupForms
     * @param  list<array{lookup_form:string,entry:array<string,mixed>}>  $results
     */
    public function __construct(
        public string $word,
        public string $databasePath,
        public NormalizationProfileEnum $profile,
        public array $lookupForms,
        public array $results,
    ) {}

    /**
     * @return array{
     *     word:string,
     *     database:string,
     *     profile:string,
     *     lookup_forms:list<string>,
     *     results:list<array{lookup_form:string,entry:array<string,mixed>}>
     * }
     */
    public function toArray(): array
    {
        return [
            'word' => $this->word,
            'database' => $this->databasePath,
            'profile' => $this->profile->value,
            'lookup_forms' => $this->lookupForms,
            'results' => $this->results,
        ];
    }
}
