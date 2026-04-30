<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;
use Dujana\ArabicNlp\Morphology\Root\DatabaseRootExtractor;

it('uses database roots for trusted known words', function (): void {
    $path = sys_get_temp_dir().'/dujana-root-quality-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add('manual', 'بحور', 'بحر', 'بحور', confidence: 0.98);
    $builder->add('manual', 'أقلام', 'قلم', 'أقلام', confidence: 0.98);

    $builder->write(clear: false);

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    foreach ([
        'بحور' => 'بحر',
        'أقلام' => 'قلم',
    ] as $word => $root) {
        $analysis = $analyzer->analyze($word, StemmerModeEnum::Root);

        expect($analysis->root)->toBe($root)
            ->and($analysis->rootAnalysis)->not->toBeNull()
            ->and($analysis->rootAnalysis->reliable())->toBeTrue()
            ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
    }
});

it('does not let database override very short ambiguous forms', function (): void {
    $path = sys_get_temp_dir().'/dujana-short-ambiguous-'.uniqid().'.sqlite';

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

it('does not let database extractor use very short ambiguous forms', function (): void {
    $path = sys_get_temp_dir().'/dujana-short-ambiguous-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add('qabas', 'ان', 'أين', 'ان', confidence: 0.99);

    $builder->write(clear: false);

    $extractor = new DatabaseRootExtractor(new LexiconLookup($database));

    expect($extractor->extract(new CoreCandidate(
        originalSurface: 'ان',
        normalized: 'ان',
        core: 'ان',
    )))->toBe([]);
});

it('keeps systematic rule roots reliable without database', function (): void {
    $analyzer = ArabicAnalyzer::make();

    foreach ([
        'مدّ' => 'مدد',
        'يكتب' => 'كتب',
        'تعلّم' => 'علم',
        'استخرج' => 'خرج',
    ] as $word => $root) {
        $analysis = $analyzer->analyze($word, StemmerModeEnum::Root);

        expect($analysis->root)->toBe($root)
            ->and($analysis->rootAnalysis)->not->toBeNull()
            ->and($analysis->rootAnalysis->reliable())->toBeTrue();
    }
});

it('keeps lexical legacy forms non-authoritative without database', function (): void {
    $analyzer = ArabicAnalyzer::make();

    foreach (['قال', 'مصري', 'أسماء', 'أقلام'] as $word) {
        $analysis = $analyzer->analyze($word, StemmerModeEnum::Root);

        expect($analysis->rootAnalysis?->reliable() ?? false)->toBeFalse();
    }
});

it('falls back safely for unsupported words', function (): void {
    $analysis = ArabicAnalyzer::make()
        ->analyze('ززززز', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('ززززز')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBeFalse()
        ->and($analysis->rootAnalysis->best->source)->toBe('fallback_core');
});
