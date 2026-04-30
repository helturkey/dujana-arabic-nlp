<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('exposes database alternatives and trust reasons in analyzer root analysis', function (): void {
    $path = sys_get_temp_dir().'/dujana-root-db-trust-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add('qabas', 'ابواب', 'باب', 'ابواب', confidence: 0.94);
    $builder->add('manual', 'ابواب', 'بوب', 'ابواب', confidence: 0.98);

    $builder->write(clear: false);

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    $analysis = $analyzer->analyze('أبواب', StemmerModeEnum::Root);
    $array = $analysis->toArray();

    expect($array['root_analysis'])->not->toBeNull()
        ->and($array['root_analysis']['source'])->toBeIn(['manual_lexicon', 'database'])
        ->and($array['root_analysis']['candidates'][0]['reasons'])->toContain('trust:manual_source_authoritative');

    $sources = array_column($array['root_analysis']['candidates'], 'source');

    expect(array_intersect($sources, ['manual_lexicon', 'database']))->not->toBeEmpty();
    expect($sources)->toContain('database_alternative');

    $databaseCandidate = collect($array['root_analysis']['candidates'])
        ->first(fn (array $candidate): bool => $candidate['source'] === 'database');

    if ($databaseCandidate !== null) {
        expect($databaseCandidate['reasons'])->toContain('trust:alternatives_penalty');
    }
});

it('does not let database resolve very short ambiguous words in analyzer root mode', function (): void {
    $path = sys_get_temp_dir().'/dujana-root-short-db-trust-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add('qabas', 'ان', 'أين', 'ان', confidence: 0.99);

    $builder->write(clear: false);

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    $analysis = $analyzer->analyze('ان', StemmerModeEnum::Root);

    if ($analysis->rootAnalysis === null) {
        expect($analysis->root)->toBeNull();

        return;
    }

    expect($analysis->rootAnalysis->best->source)->not->toBeIn(['manual_lexicon', 'database']);
});

it('prefers original surface database lookup over stripped core lookup', function (): void {
    $path = sys_get_temp_dir().'/dujana-original-vs-core-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    // Core-like form would be wrong if selected first.
    $builder->add('qabas', 'عباس', 'عبس', 'عباس', confidence: 0.99);

    // Surface/manual form is the correct override.
    $builder->add('manual', 'عباسي', 'عباس', 'عباسي', pos: 'نسبة', confidence: 0.98);

    $builder->write(clear: false);

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    $analysis = $analyzer->analyze('عباسي', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('عباس')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database'])
        ->and($analysis->rootAnalysis->best->reasons)->toContain('trust:original_surface_lookup_bonus');
});
