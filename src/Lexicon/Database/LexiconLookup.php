<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use Dujana\ArabicNlp\Enums\NormalizationProfileEnum;
use Dujana\ArabicNlp\Text\ArabicNormalizer;
use InvalidArgumentException;
use PDO;

final class LexiconLookup
{
    private const HARAKAT_PATTERN = '/[\x{064B}-\x{0652}]/u';

    private const MAX_LOOKUP_LENGTH = 128;

    private const MAX_LOOKUP_FORMS = 12;

    private const MAX_RESULTS = 20;

    public function __construct(
        private readonly LexiconDatabase $database,
        private readonly ArabicNormalizer $normalizer = new ArabicNormalizer,
    ) {}

    /**
     * Exact lookup only.
     *
     * @return list<LexiconEntry>
     */
    public function lookupExact(string $normalizedForm): array
    {
        return array_map(
            static fn (array $row): LexiconEntry => $row['entry'],
            $this->lookupManyExact([$normalizedForm]),
        );
    }

    /**
     * Variant-aware lookup.
     *
     * @return list<LexiconEntry>
     */
    public function lookup(string $form, NormalizationProfileEnum $profile = NormalizationProfileEnum::Search): array
    {
        return array_map(
            static fn (array $row): LexiconEntry => $row['entry'],
            $this->lookupWithForms($form, $profile),
        );
    }

    /**
     * Variant-aware lookup that preserves the matched lookup form.
     *
     * @return list<array{lookup_form:string,entry:LexiconEntry}>
     */
    public function lookupWithForms(
        string $form,
        NormalizationProfileEnum $profile = NormalizationProfileEnum::Search,
    ): array {
        return $this->lookupManyExact(
            $this->lookupForms($form, $profile),
        );
    }

    /**
     * Exact lookup for multiple normalized forms in one query.
     *
     * @param  list<string>  $normalizedForms
     * @return list<array{lookup_form:string,entry:LexiconEntry}>
     */
    public function lookupManyExact(array $normalizedForms): array
    {
        $forms = array_values(array_unique(array_filter(array_map(
            static fn (string $form): string => trim($form),
            $normalizedForms,
        ))));

        if ($forms === []) {
            return [];
        }

        $forms = array_slice($forms, 0, self::MAX_LOOKUP_FORMS);

        $placeholders = implode(',', array_fill(0, count($forms), '?'));

        $stmt = $this->database->pdo()->prepare(
            "SELECT *
             FROM lexical_entries
             WHERE normalized_form IN ({$placeholders})
             ORDER BY confidence DESC, source_count DESC
             LIMIT ".self::MAX_RESULTS
        );

        $stmt->execute($forms);

        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if ($rows === []) {
            return [];
        }

        return $this->uniqueLookupRows(
            $this->hydrateRowsWithSources($rows),
        );
    }

    /**
     * @return list<string>
     */
    public function lookupForms(
        string $form,
        NormalizationProfileEnum $profile = NormalizationProfileEnum::Search,
    ): array {
        $form = trim($form);

        if ($form === '') {
            return [];
        }

        if (mb_strlen($form) > self::MAX_LOOKUP_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Lexicon lookup form exceeds the maximum length of %d characters.',
                self::MAX_LOOKUP_LENGTH,
            ));
        }

        $search = $this->normalizer->normalize($form, NormalizationProfileEnum::Search);
        $morphology = $this->normalizer->normalize($form, NormalizationProfileEnum::Morphology);
        $stemming = $this->normalizer->normalize($form, NormalizationProfileEnum::Stemming);
        $selected = $this->normalizer->normalize($form, $profile);

        $forms = [
            $form,
            $selected,
            $search,
            $morphology,
            $stemming,
            $this->stripHarakat($form),
            $this->stripHarakat($selected),
            $this->stripHarakat($search),
            $this->stripHarakat($morphology),
            $this->stripHarakat($stemming),
        ];

        return array_slice(
            array_values(array_unique(array_filter($forms))),
            0,
            self::MAX_LOOKUP_FORMS,
        );
    }

    /**
     * @param  list<array<string,mixed>>  $rows
     * @return list<array{lookup_form:string,entry:LexiconEntry}>
     */
    private function hydrateRowsWithSources(array $rows): array
    {
        $sourcesByEntryId = $this->hydrateSourcesForEntries(
            array_map(
                static fn (array $row): int => (int) $row['id'],
                $rows,
            ),
        );

        $results = [];

        foreach ($rows as $row) {
            $entryId = (int) $row['id'];
            $sources = $sourcesByEntryId[$entryId] ?? [];
            $entryRoot = $row['root'] !== null ? (string) $row['root'] : null;

            $results[] = [
                'lookup_form' => (string) $row['normalized_form'],
                'entry' => $this->hydrateEntry(
                    row: $row,
                    sources: $sources,
                    alternatives: $this->alternativesFromSources($sources, $entryRoot),
                ),
            ];
        }

        return $results;
    }

    /**
     * @param  array<string,mixed>  $row
     * @param  list<LexiconSource>  $sources
     * @param  list<array{root:string,confidence:float,sources:list<string>}>  $alternatives
     */
    private function hydrateEntry(array $row, array $sources, array $alternatives): LexiconEntry
    {
        return new LexiconEntry(
            normalizedForm: (string) $row['normalized_form'],
            lemma: $row['lemma'] !== null ? (string) $row['lemma'] : null,
            normalizedLemma: $row['normalized_lemma'] !== null ? (string) $row['normalized_lemma'] : null,
            root: $row['root'] !== null ? (string) $row['root'] : null,
            posCat: $row['pos_cat'] !== null ? (string) $row['pos_cat'] : null,
            pos: $row['pos'] !== null ? (string) $row['pos'] : null,
            language: $row['language'] !== null ? (string) $row['language'] : null,
            confidence: (float) $row['confidence'],
            sourceCount: (int) $row['source_count'],
            sources: $sources,
            alternatives: $alternatives,
        );
    }

    /**
     * @param  list<int>  $entryIds
     * @return array<int,list<LexiconSource>>
     */
    private function hydrateSourcesForEntries(array $entryIds): array
    {
        $entryIds = array_values(array_unique(array_filter(
            $entryIds,
            static fn (int $id): bool => $id > 0,
        )));

        if ($entryIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($entryIds), '?'));

        $stmt = $this->database->pdo()->prepare(
            "SELECT *
             FROM lexical_sources
             WHERE entry_id IN ({$placeholders})
             ORDER BY entry_id ASC, confidence DESC"
        );

        $stmt->execute($entryIds);

        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        /** @var array<int,list<LexiconSource>> $sources */
        $sources = [];

        foreach ($rows as $sourceRow) {
            $entryId = (int) $sourceRow['entry_id'];

            $sources[$entryId] ??= [];

            $sources[$entryId][] = new LexiconSource(
                source: (string) $sourceRow['source'],
                sourceLemma: $sourceRow['source_lemma'] !== null ? (string) $sourceRow['source_lemma'] : null,
                sourceRoot: $sourceRow['source_root'] !== null ? (string) $sourceRow['source_root'] : null,
                sourcePos: $sourceRow['source_pos'] !== null ? (string) $sourceRow['source_pos'] : null,
                sourcePayload: $sourceRow['source_payload'] !== null ? (string) $sourceRow['source_payload'] : null,
                confidence: (float) $sourceRow['confidence'],
            );
        }

        return $sources;
    }

    /**
     * @param  list<array{lookup_form:string,entry:LexiconEntry}>  $rows
     * @return list<array{lookup_form:string,entry:LexiconEntry}>
     */
    private function uniqueLookupRows(array $rows): array
    {
        $unique = [];

        foreach ($rows as $row) {
            $entry = $row['entry'];

            $key = implode('|', [
                $entry->normalizedForm,
                $entry->root ?? '',
                $entry->lemma ?? '',
            ]);

            $unique[$key] ??= $row;
        }

        return array_values($unique);
    }

    /**
     * Alternatives are roots preserved in lexical_sources that differ from lexical_entries.root.
     *
     * @param  list<LexiconSource>  $sources
     * @return list<array{root:string,confidence:float,sources:list<string>}>
     */
    private function alternativesFromSources(array $sources, ?string $entryRoot): array
    {
        $alternatives = [];

        foreach ($sources as $source) {
            if ($source->sourceRoot === null || $source->sourceRoot === $entryRoot) {
                continue;
            }

            $alternatives[$source->sourceRoot] ??= [
                'root' => $source->sourceRoot,
                'confidence' => $source->confidence,
                'sources' => [],
            ];

            $alternatives[$source->sourceRoot]['confidence'] = max(
                $alternatives[$source->sourceRoot]['confidence'],
                $source->confidence,
            );

            $alternatives[$source->sourceRoot]['sources'][] = $source->source;
            $alternatives[$source->sourceRoot]['sources'] = array_values(
                array_unique($alternatives[$source->sourceRoot]['sources']),
            );
        }

        return array_values($alternatives);
    }

    private function stripHarakat(string $word): string
    {
        return preg_replace(self::HARAKAT_PATTERN, '', $word) ?? $word;
    }
}
