<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Lexicon\Importers\ManualTsvImporter;
use Dujana\ArabicNlp\Morphology\Root\DatabaseRootExtractor;

it('builds a local sqlite lexicon and looks up roots', function (): void {
    $path = sys_get_temp_dir().'/dujana-lexicon-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->add('manual', 'مدارس', 'درس', 'مدارس', 'اسم', 'اسم', 'فصحى حديثة', 0.98);

    $count = $builder->write();

    $entries = (new LexiconLookup($database))->lookup('مدارس');

    expect($count)->toBe(1)
        ->and($entries)->toHaveCount(1)
        ->and($entries[0]->root)->toBe('درس')
        ->and($entries[0]->sources[0]->source)->toBe('manual');
});

it('does not overwrite conflicting roots when forms normalize to the same entry', function (): void {
    $path = sys_get_temp_dir().'/dujana-lexicon-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->add('qabas', 'أبواب', 'باب', 'أبواب', confidence: 0.94);
    $builder->add('manual', 'ابواب', 'بوب', 'ابواب', confidence: 0.98);

    $builder->write();

    $entries = (new LexiconLookup($database))->lookup('ابواب');

    expect($entries)->toHaveCount(1);

    $entry = $entries[0];

    expect($entry->root)->toBe('بوب')
        ->and($entry->sources)->toHaveCount(2);

    $sourceRoots = array_map(static fn ($source): ?string => $source->sourceRoot, $entry->sources);
    sort($sourceRoots);

    expect($sourceRoots)->toBe(['باب', 'بوب']);

    $alternatives = array_map(static fn (array $alternative): string => $alternative['root'], $entry->alternatives);

    expect($alternatives)->toContain('باب');
});

it('imports manual tsv rows', function (): void {
    $dbPath = sys_get_temp_dir().'/dujana-lexicon-'.uniqid().'.sqlite';
    $tsvPath = sys_get_temp_dir().'/dujana-manual-'.uniqid().'.tsv';

    file_put_contents($tsvPath, "أقلام\tقلم\tاسم\tاسم\tفصحى حديثة\n");

    $database = new LexiconDatabase($dbPath);
    $builder = new LexiconBuilder($database);

    $imported = (new ManualTsvImporter($builder))->import($tsvPath);
    $entries = $builder->write();

    $lookup = new LexiconLookup($database);

    expect($imported)->toBe(1)
        ->and($entries)->toBe(1)
        ->and($lookup->lookup('اقلام')[0]->root)->toBe('قلم');
});

it('extracts database root candidates', function (): void {
    $path = sys_get_temp_dir().'/dujana-lexicon-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->add('manual', 'بحور', 'بحر', confidence: 0.98);
    $builder->write();

    $extractor = new DatabaseRootExtractor(new LexiconLookup($database));
    $candidates = $extractor->extract(new CoreCandidate(originalSurface: 'بحور', normalized: 'بحور', core: 'بحور'));

    expect($candidates)->not->toBeEmpty();

    $sources = array_map(static fn ($candidate): string => $candidate->source, $candidates);

    expect(array_intersect($sources, ['manual_lexicon', 'database']))->not->toBeEmpty();

    $best = $candidates[0];

    expect($best->root)->toBe('بحر')
        ->and($best->source)->toBeIn(['manual_lexicon', 'database']);
});
