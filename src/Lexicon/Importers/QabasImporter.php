<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Importers;

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use RuntimeException;
use SplFileObject;

final readonly class QabasImporter
{
    public function __construct(
        private LexiconBuilder $builder,
        private bool $modernOnly = true,
    ) {}

    public function import(string $csvPath): int
    {
        if (! is_file($csvPath)) {
            throw new RuntimeException("Qabas CSV file not found [{$csvPath}].");
        }

        $file = new SplFileObject($csvPath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $file->setCsvControl(',');

        $headers = null;
        $count = 0;

        foreach ($file as $row) {
            if ($row === [null] || $row === false) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(
                    static fn ($value): string => trim((string) $value),
                    $row
                );

                continue;
            }

            $data = $this->combine($headers, $row);

            $lemma = $data['lemma'] ?? null;
            $root = $data['root'] ?? null;
            $language = $data['language'] ?? null;

            if ($lemma === null || $root === null || trim($lemma) === '' || trim($root) === '') {
                continue;
            }

            if ($this->modernOnly && $language !== null && ! str_contains($language, 'فصحى')) {
                continue;
            }

            $this->builder->add(
                source: 'qabas',
                form: $lemma,
                root: $root,
                lemma: $lemma,
                posCat: $data['pos_cat'] ?? null,
                pos: $data['pos'] ?? null,
                language: $language,
                confidence: 0.94,
                payload: json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null,
            );

            $count++;
        }

        return $count;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<mixed>  $row
     * @return array<string,string|null>
     */
    private function combine(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $value = $row[$index] ?? null;
            $data[$header] = $value !== null ? trim((string) $value) : null;
        }

        return $data;
    }
}
