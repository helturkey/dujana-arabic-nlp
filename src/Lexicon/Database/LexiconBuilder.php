<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Lexicon\Database;

use Dujana\ArabicNlp\Text\ArabicNormalizer;
use LogicException;
use PDO;
use PDOStatement;

final class LexiconBuilder
{
    private const MAX_FORM_LENGTH = 128;

    private const MAX_ROOT_LENGTH = 32;

    private const MAX_META_LENGTH = 128;

    private const MAX_PAYLOAD_LENGTH = 4096;

    private bool $started = false;

    private bool $transactionActive = false;

    private int $sourceRows = 0;

    private ?PDOStatement $findEntryStmt = null;

    private ?PDOStatement $insertEntryStmt = null;

    private ?PDOStatement $insertSourceStmt = null;

    private ?PDOStatement $updateEntryStmt = null;

    private ?PDOStatement $entrySourcesStmt = null;

    public function __construct(
        private readonly LexiconDatabase $database,
        private readonly ArabicNormalizer $normalizer = new ArabicNormalizer,
    ) {}

    /**
     * Start a streaming build.
     *
     * Use this explicitly from the build command before importers run:
     *
     * $builder->begin(clear: ! $append);
     */
    public function begin(bool $clear = true): void
    {
        if ($this->started) {
            return;
        }

        $this->database->migrate();

        if ($clear) {
            $this->database->clear();
        }

        $this->database->pdo()->beginTransaction();

        $this->transactionActive = true;
        $this->started = true;

        $this->prepareStatements();
    }

    public function add(
        string $source,
        string $form,
        ?string $root,
        ?string $lemma = null,
        ?string $posCat = null,
        ?string $pos = null,
        ?string $language = null,
        float $confidence = 0.80,
        ?string $payload = null,
    ): void {
        /*
         * Backward-compatible behavior:
         * if the caller did not explicitly call begin(), start a fresh build.
         *
         * For append mode, callers must explicitly call:
         *
         * $builder->begin(clear: false);
         */
        if (! $this->started) {
            $this->begin(clear: true);
        }

        $input = $this->sanitizeInput(
            source: $source,
            form: $form,
            root: $root,
            lemma: $lemma,
            posCat: $posCat,
            pos: $pos,
            language: $language,
            payload: $payload,
        );

        if ($input === null) {
            return;
        }

        foreach ($this->splitForms($input['form']) as $singleForm) {
            $normalizedForm = $this->normalizer->normalize($singleForm);

            if ($normalizedForm === '') {
                continue;
            }

            $sourceLemma = $this->safeNonEmptyField(
                value: $input['lemma'],
                fallback: $singleForm,
                maxLength: self::MAX_FORM_LENGTH,
            );

            $normalizedLemma = $this->normalizer->normalize($sourceLemma);

            if ($normalizedLemma === '') {
                $normalizedLemma = $normalizedForm;
            }

            $entryId = $this->findOrCreateEntry(
                normalizedForm: $normalizedForm,
                lemma: $sourceLemma,
                normalizedLemma: $normalizedLemma,
            );

            $this->insertSource(
                entryId: $entryId,
                source: $input['source'],
                sourceLemma: $sourceLemma,
                sourceRoot: $input['root'],
                sourcePos: $input['pos'],
                sourcePayload: $input['payload'],
                confidence: $this->clampConfidence($confidence),
            );

            $this->refreshEntryBestRoot(
                entryId: $entryId,
                fallbackLemma: $sourceLemma,
                fallbackNormalizedLemma: $normalizedLemma,
                fallbackPosCat: $input['pos_cat'],
                fallbackPos: $input['pos'],
                fallbackLanguage: $input['language'],
            );

            $this->sourceRows++;
        }
    }

    /**
     * Finalize streaming build.
     *
     * The $clear argument is kept for backward compatibility.
     * If no add() happened yet, write($clear) starts and finalizes an empty build.
     */
    public function write(bool $clear = true): int
    {
        if (! $this->started) {
            $this->begin(clear: $clear);
        }

        if ($this->transactionActive) {
            $this->database->pdo()->commit();
            $this->transactionActive = false;
        }

        return $this->entriesCount();
    }

    public function rollback(): void
    {
        if (! $this->transactionActive) {
            return;
        }

        $this->database->pdo()->rollBack();
        $this->transactionActive = false;
    }

    public function importedRows(): int
    {
        return $this->sourceRows;
    }

    private function prepareStatements(): void
    {
        $pdo = $this->database->pdo();

        $this->findEntryStmt = $pdo->prepare(<<<'SQL'
SELECT id
FROM lexical_entries
WHERE normalized_form = :normalized_form
LIMIT 1
SQL);

        $this->insertEntryStmt = $pdo->prepare(<<<'SQL'
INSERT INTO lexical_entries (
    normalized_form,
    lemma,
    normalized_lemma,
    root,
    pos_cat,
    pos,
    language,
    confidence,
    source_count,
    created_at,
    updated_at
) VALUES (
    :normalized_form,
    :lemma,
    :normalized_lemma,
    :root,
    :pos_cat,
    :pos,
    :language,
    :confidence,
    :source_count,
    :created_at,
    :updated_at
)
SQL);

        $this->insertSourceStmt = $pdo->prepare(<<<'SQL'
INSERT INTO lexical_sources (
    entry_id,
    source,
    source_lemma,
    source_root,
    source_pos,
    source_payload,
    confidence
) VALUES (
    :entry_id,
    :source,
    :source_lemma,
    :source_root,
    :source_pos,
    :source_payload,
    :confidence
)
SQL);

        $this->updateEntryStmt = $pdo->prepare(<<<'SQL'
UPDATE lexical_entries
SET
    lemma = :lemma,
    normalized_lemma = :normalized_lemma,
    root = :root,
    pos_cat = :pos_cat,
    pos = :pos,
    language = :language,
    confidence = :confidence,
    source_count = :source_count,
    updated_at = :updated_at
WHERE id = :id
SQL);

        $this->entrySourcesStmt = $pdo->prepare(<<<'SQL'
SELECT
    source,
    source_lemma,
    source_root,
    source_pos,
    confidence
FROM lexical_sources
WHERE entry_id = :entry_id
  AND source_root IS NOT NULL
  AND source_root != ''
SQL);
    }

    private function findOrCreateEntry(
        string $normalizedForm,
        string $lemma,
        string $normalizedLemma,
    ): int {
        $stmt = $this->statement($this->findEntryStmt, 'findEntry');

        $stmt->execute([
            'normalized_form' => $normalizedForm,
        ]);

        $existingId = $stmt->fetchColumn();

        if ($existingId !== false && $existingId !== null) {
            return (int) $existingId;
        }

        $now = date('c');

        $this->statement($this->insertEntryStmt, 'insertEntry')->execute([
            'normalized_form' => $normalizedForm,
            'lemma' => $lemma,
            'normalized_lemma' => $normalizedLemma,
            'root' => null,
            'pos_cat' => null,
            'pos' => null,
            'language' => null,
            'confidence' => 0.0,
            'source_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->database->pdo()->lastInsertId();
    }

    private function insertSource(
        int $entryId,
        string $source,
        string $sourceLemma,
        string $sourceRoot,
        ?string $sourcePos,
        ?string $sourcePayload,
        float $confidence,
    ): void {
        $this->statement($this->insertSourceStmt, 'insertSource')->execute([
            'entry_id' => $entryId,
            'source' => $source,
            'source_lemma' => $sourceLemma,
            'source_root' => $sourceRoot,
            'source_pos' => $sourcePos,
            'source_payload' => $sourcePayload,
            'confidence' => $confidence,
        ]);
    }

    private function refreshEntryBestRoot(
        int $entryId,
        string $fallbackLemma,
        string $fallbackNormalizedLemma,
        ?string $fallbackPosCat,
        ?string $fallbackPos,
        ?string $fallbackLanguage,
    ): void {
        $stmt = $this->statement($this->entrySourcesStmt, 'entrySources');

        $stmt->execute([
            'entry_id' => $entryId,
        ]);

        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if ($rows === []) {
            return;
        }

        $roots = $this->collectRootScores(
            rows: $rows,
            fallbackLemma: $fallbackLemma,
            fallbackNormalizedLemma: $fallbackNormalizedLemma,
            fallbackPosCat: $fallbackPosCat,
            fallbackPos: $fallbackPos,
            fallbackLanguage: $fallbackLanguage,
        );

        if ($roots === []) {
            return;
        }

        [$root, $rootData] = $this->chooseRoot($roots);

        $sourceCount = count($rootData['sources']);
        $confidence = min(
            0.99,
            $rootData['score'] + (0.04 * max(0, $sourceCount - 1)),
        );

        $this->statement($this->updateEntryStmt, 'updateEntry')->execute([
            'id' => $entryId,
            'lemma' => $rootData['lemma'],
            'normalized_lemma' => $rootData['normalized_lemma'],
            'root' => $root,
            'pos_cat' => $rootData['pos_cat'],
            'pos' => $rootData['pos'],
            'language' => $rootData['language'],
            'confidence' => $confidence,
            'source_count' => $sourceCount,
            'updated_at' => date('c'),
        ]);
    }

    /**
     * @param  list<array<string,mixed>>  $rows
     * @return array<string,array{
     *     score:float,
     *     sources:array<string,true>,
     *     pos_cat:?string,
     *     pos:?string,
     *     language:?string,
     *     lemma:string,
     *     normalized_lemma:string
     * }>
     */
    private function collectRootScores(
        array $rows,
        string $fallbackLemma,
        string $fallbackNormalizedLemma,
        ?string $fallbackPosCat,
        ?string $fallbackPos,
        ?string $fallbackLanguage,
    ): array {
        $roots = [];

        foreach ($rows as $row) {
            $root = (string) ($row['source_root'] ?? '');

            if ($root === '') {
                continue;
            }

            $source = (string) ($row['source'] ?? '');
            $confidence = (float) ($row['confidence'] ?? 0.0);

            $roots[$root] ??= [
                'score' => 0.0,
                'sources' => [],
                'pos_cat' => $fallbackPosCat,
                'pos' => $fallbackPos,
                'language' => $fallbackLanguage,
                'lemma' => $fallbackLemma,
                'normalized_lemma' => $fallbackNormalizedLemma,
            ];

            $roots[$root]['score'] = max(
                $roots[$root]['score'],
                $confidence + $this->sourcePriorityBonus($source),
            );

            $roots[$root]['sources'][$source] = true;

            if (($row['source_pos'] ?? null) !== null && $roots[$root]['pos'] === null) {
                $roots[$root]['pos'] = (string) $row['source_pos'];
            }

            if (($row['source_lemma'] ?? null) !== null && trim((string) $row['source_lemma']) !== '') {
                $sourceLemma = $this->safeField((string) $row['source_lemma'], self::MAX_FORM_LENGTH);

                if ($sourceLemma !== '') {
                    $roots[$root]['lemma'] = $sourceLemma;
                    $roots[$root]['normalized_lemma'] = $this->normalizer->normalize($sourceLemma);
                }
            }
        }

        return $roots;
    }

    /**
     * @param array<string,array{
     *     score:float,
     *     sources:array<string,true>,
     *     pos_cat:?string,
     *     pos:?string,
     *     language:?string,
     *     lemma:string,
     *     normalized_lemma:string
     * }> $roots
     * @return array{
     *     0:string,
     *     1:array{
     *         score:float,
     *         sources:array<string,true>,
     *         pos_cat:?string,
     *         pos:?string,
     *         language:?string,
     *         lemma:string,
     *         normalized_lemma:string
     *     }
     * }
     */
    private function chooseRoot(array $roots): array
    {
        uasort($roots, static function (array $a, array $b): int {
            $aScore = $a['score'] + (0.04 * max(0, count($a['sources']) - 1));
            $bScore = $b['score'] + (0.04 * max(0, count($b['sources']) - 1));

            return $bScore <=> $aScore;
        });

        $root = array_key_first($roots);

        if ($root === null) {
            throw new LogicException('Cannot choose a root from an empty root score map.');
        }

        return [$root, $roots[$root]];
    }

    private function sourcePriorityBonus(string $source): float
    {
        return match ($source) {
            'manual' => 0.10,
            'qabas' => 0.06,
            'arramooz' => 0.03,
            default => 0.0,
        };
    }

    /**
     * @return list<string>
     */
    private function splitForms(string $form): array
    {
        $parts = preg_split('/\s*[|،,؛]\s*/u', $form);

        if ($parts === false) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $part): string => $this->safeField($part, self::MAX_FORM_LENGTH),
            $parts,
        )));
    }

    private function normalizeRoot(string $root): string
    {
        $root = trim($root);

        /*
         * Roots may arrive as:
         * ك ت ب
         * ك-ت-ب
         * ك_ت_ب
         *
         * But unlike forms, roots must preserve hamza identity:
         *
         * سأل must not become سال
         * قرأ must not become قرا
         * أكل must not become اكل
         */
        $root = preg_replace('/[\s\-_]+/u', '', $root) ?? $root;
        $root = str_replace('ـ', '', $root);

        $root = preg_replace(
            '/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u',
            '',
            $root,
        ) ?? $root;

        $root = str_replace('ى', 'ي', $root);

        return trim($root);
    }

    private function entriesCount(): int
    {
        $count = $this->database
            ->pdo()
            ->query('SELECT COUNT(*) FROM lexical_entries')
            ->fetchColumn();

        return (int) $count;
    }

    private function statement(?PDOStatement $statement, string $name): PDOStatement
    {
        if ($statement === null) {
            throw new LogicException("LexiconBuilder statement [{$name}] was not prepared.");
        }

        return $statement;
    }

    /**
     * @return array{
     *     source:string,
     *     form:string,
     *     root:string,
     *     lemma:?string,
     *     pos_cat:?string,
     *     pos:?string,
     *     language:?string,
     *     payload:?string
     * }|null
     */
    private function sanitizeInput(
        string $source,
        string $form,
        ?string $root,
        ?string $lemma,
        ?string $posCat,
        ?string $pos,
        ?string $language,
        ?string $payload,
    ): ?array {
        $source = $this->safeField($source, self::MAX_META_LENGTH);
        $form = $this->safeField($form, self::MAX_FORM_LENGTH);
        $root = $root !== null ? $this->safeField($root, self::MAX_ROOT_LENGTH) : null;

        if ($source === '' || $form === '' || $root === null || $root === '') {
            return null;
        }

        $normalizedRoot = $this->normalizeRoot($root);

        if ($normalizedRoot === '') {
            return null;
        }

        return [
            'source' => $source,
            'form' => $form,
            'root' => $normalizedRoot,
            'lemma' => $this->safeNullableField($lemma, self::MAX_FORM_LENGTH),
            'pos_cat' => $this->safeNullableField($posCat, self::MAX_META_LENGTH),
            'pos' => $this->safeNullableField($pos, self::MAX_META_LENGTH),
            'language' => $this->safeNullableField($language, self::MAX_META_LENGTH),
            'payload' => $this->safeNullableField($payload, self::MAX_PAYLOAD_LENGTH),
        ];
    }

    private function safeField(string $value, int $maxLength): string
    {
        $value = trim($value);

        if (mb_strlen($value) > $maxLength) {
            return mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    private function safeNullableField(?string $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = $this->safeField($value, $maxLength);

        return $value === '' ? null : $value;
    }

    private function safeNonEmptyField(?string $value, string $fallback, int $maxLength): string
    {
        $value = $this->safeNullableField($value, $maxLength);

        if ($value !== null) {
            return $value;
        }

        return $this->safeField($fallback, $maxLength);
    }

    private function clampConfidence(float $confidence): float
    {
        return max(0.0, min(1.0, $confidence));
    }
}
