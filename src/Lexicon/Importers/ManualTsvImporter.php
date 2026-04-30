<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Importers;

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Manual\ManualRootsReader;

final readonly class ManualTsvImporter
{
    public function __construct(
        private LexiconBuilder $builder,
        private ?ManualRootsReader $reader = null,
    ) {}

    public function import(string $path): int
    {
        $reader = $this->reader ?? new ManualRootsReader;

        $count = 0;

        foreach ($reader->read($path) as $entry) {
            $this->builder->add(
                source: 'manual',
                form: $entry->form,
                root: $entry->root,
                lemma: $entry->lemma ?? $entry->form,
                posCat: $entry->posCat,
                pos: $entry->pos,
                language: $entry->language ?? 'فصحى',
                confidence: $entry->confidence,
            );

            $count++;
        }

        return $count;
    }
}
