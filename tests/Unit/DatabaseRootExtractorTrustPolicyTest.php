<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Morphology\Root\DatabaseRootExtractor;

it('does not use database roots for very short ambiguous forms', function (): void {
    $path = sys_get_temp_dir().'/dujana-db-short-form-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('qabas', 'ان', 'أين', 'ان', confidence: 0.95);
    $builder->write(clear: false);

    $extractor = new DatabaseRootExtractor(new LexiconLookup($database));

    expect($extractor->extract(new CoreCandidate(
        originalSurface: 'بحر',
        normalized: 'بحر',
        core: 'بحر', )))
        ->toBe([]);
});

it('adds trust reasons to database root candidates', function (): void {
    $path = sys_get_temp_dir().'/dujana-db-trust-reasons-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('qabas', 'بحور', 'بحر', 'بحور', confidence: 0.88);
    $builder->add('manual', 'بحور', 'بحر', 'بحور', confidence: 0.98);
    $builder->write(clear: false);

    $extractor = new DatabaseRootExtractor(new LexiconLookup($database));

    $candidates = $extractor->extract(new CoreCandidate(originalSurface: 'بحور', normalized: 'بحور', core: 'بحور'));

    expect($candidates)->not->toBeEmpty()
        ->and($candidates[0]->root)->toBe('بحر')
        ->and($candidates[0]->source)->toBeIn(['manual_lexicon', 'database'])
        ->and($candidates[0]->reasons)->toContain('trust:manual_source_authoritative');

    $databaseCandidate = collect($candidates)
        ->first(fn ($candidate) => $candidate->source === 'database');

    if ($databaseCandidate !== null) {
        expect($databaseCandidate->reasons)->toContain('trust:multiple_sources_bonus');
    }
});

it('returns database alternatives with lower confidence', function (): void {
    $path = sys_get_temp_dir().'/dujana-db-alternatives-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('qabas', 'ابواب', 'باب', 'ابواب', confidence: 0.94);
    $builder->add('manual', 'ابواب', 'بوب', 'ابواب', confidence: 0.98);
    $builder->write(clear: false);

    $extractor = new DatabaseRootExtractor(new LexiconLookup($database));

    $candidates = $extractor->extract(new CoreCandidate(originalSurface: 'ابواب', normalized: 'ابواب', core: 'ابواب'));

    $sources = array_map(static fn ($candidate): string => $candidate->source, $candidates);

    expect(array_intersect($sources, ['manual_lexicon', 'database']))->not->toBeEmpty()
        ->and($sources)->toContain('database_alternative');

    $best = collect($candidates)->first(
        fn ($candidate) => in_array($candidate->source, ['manual_lexicon', 'database'], true)
    );
    $alternative = collect($candidates)->first(fn ($candidate) => $candidate->source === 'database_alternative');

    expect($best)->not->toBeNull()
        ->and($alternative)->not->toBeNull()
        ->and($alternative->confidence)->toBeLessThan($best->confidence)
        ->and($alternative->reasons)->toContain('trust:alternative_root');
});
