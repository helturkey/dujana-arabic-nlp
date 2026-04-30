<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Importers;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Text\ArabicNormalizer;
use PDO;
use RuntimeException;

final readonly class ArramoozImporter
{
    public function __construct(
        private LexiconBuilder $builder,
        private ArabicNormalizer $normalizer = new ArabicNormalizer,
    ) {}

    public function import(string $sqlitePath): int
    {
        if (! is_file($sqlitePath)) {
            throw new RuntimeException("Arramooz SQLite file not found [{$sqlitePath}].");
        }

        $pdo = new PDO('sqlite:'.$sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $this->importNouns($pdo) + $this->importVerbs($pdo);
    }

    private function importNouns(PDO $pdo): int
    {
        $stmt = $pdo->query(<<<'SQL'
SELECT
    vocalized,
    unvocalized,
    normalized,
    root,
    wordtype,
    category,
    original,
    single,
    broken_plural,
    gender,
    number
FROM nouns
WHERE root IS NOT NULL
  AND trim(root) != ''
SQL);

        $count = 0;

        while (($row = $stmt->fetch()) !== false) {
            $root = $this->clean((string) ($row['root'] ?? ''));

            if ($root === '') {
                continue;
            }

            $forms = $this->nounForms($row);

            foreach ($forms as $form) {
                $this->builder->add(
                    source: 'arramooz',
                    form: $form,
                    root: $root,
                    lemma: $this->clean((string) ($row['unvocalized'] ?: $row['normalized'] ?: $form)),
                    posCat: 'اسم',
                    pos: $this->clean((string) ($row['wordtype'] ?: $row['category'] ?: 'اسم')),
                    language: 'فصحى',
                    confidence: 0.88,
                    payload: json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null,
                );

                $count++;
            }
        }

        return $count;
    }

    private function importVerbs(PDO $pdo): int
    {
        $stmt = $pdo->query(<<<'SQL'
SELECT
    vocalized,
    unvocalized,
    normalized,
    root,
    future_type,
    triliteral,
    transitive,
    past,
    future,
    imperative,
    passive
FROM verbs
WHERE root IS NOT NULL
  AND trim(root) != ''
SQL);

        $count = 0;

        while (($row = $stmt->fetch()) !== false) {
            $root = $this->clean((string) ($row['root'] ?? ''));

            if ($root === '') {
                continue;
            }

            $forms = $this->inflectedVerbSurfaces($row);

            foreach ($forms as $form) {
                $this->builder->add(
                    source: 'arramooz',
                    form: $form,
                    root: $root,
                    lemma: $this->clean((string) ($row['unvocalized'] ?: $row['normalized'] ?: $form)),
                    posCat: 'فعل',
                    pos: 'فعل',
                    language: 'فصحى',
                    confidence: 0.88,
                    payload: json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null,
                );

                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<string,mixed>  $row
     * @return list<string>
     */
    private function nounForms(array $row): array
    {
        $forms = [
            $row['unvocalized'] ?? null,
            $row['normalized'] ?? null,
            $row['original'] ?? null,
            $row['single'] ?? null,
            $row['broken_plural'] ?? null,
        ];

        /*
         * vocalized is useful too because LexiconBuilder removes diacritics.
         */
        $forms[] = $row['vocalized'] ?? null;

        return $this->uniqueCleanForms($forms);
    }

    /**
     * @param  array<string,mixed>  $row
     * @return list<string>
     */
    private function inflectedVerbSurfaces(array $row): array
    {
        return $this->uniqueCleanForms([
            $row['unvocalized'] ?? null,
            $row['normalized'] ?? null,
            $row['vocalized'] ?? null,
        ]);
    }

    /**
     * @param  list<mixed>  $forms
     * @return list<string>
     */
    private function uniqueCleanForms(array $forms): array
    {
        $clean = [];
        $seen = [];

        foreach ($forms as $form) {
            if ($form === null) {
                continue;
            }

            $form = $this->clean((string) $form);

            if ($form === '') {
                continue;
            }

            if (! $this->isUsableArabicForm($form)) {
                continue;
            }

            $key = $this->normalizer->normalize($form, NormalizationProfileEnum::Search);

            if ($key === '' || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $clean[] = $form;
        }

        return $clean;
    }

    private function isUsableArabicForm(string $form): bool
    {
        if (mb_strlen($form) < 2) {
            return false;
        }

        if (str_contains($form, '+') || str_contains($form, ':')) {
            return false;
        }

        return preg_match('/^[\p{Arabic}]+$/u', $form) === 1;
    }

    private function clean(string $value): string
    {
        $value = trim($value);

        if ($value === '' || $value === 'NULL' || $value === '-') {
            return '';
        }

        return $value;
    }
}
