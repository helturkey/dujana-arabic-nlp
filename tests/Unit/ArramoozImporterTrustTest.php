<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('does not treat non-manual database entries as manual lexicon roots', function (): void {
    $path = sys_get_temp_dir().'/dujana-arramooz-trust-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add(
        source: 'arramooz',
        form: 'أقلام',
        root: 'قلم',
        lemma: 'قلم',
        posCat: 'اسم',
        pos: 'جمع تكسير',
        language: 'فصحى',
        confidence: 0.92,
    );

    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ))->analyze('أقلام', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('قلم')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('database')
        ->and($analysis->rootAnalysis->best->source)->not->toBe('manual_lexicon');
});

it('still lets manual entries override non-manual database entries', function (): void {
    $path = sys_get_temp_dir().'/dujana-manual-overrides-arramooz-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add(
        source: 'arramooz',
        form: 'مصري',
        root: 'مصر',
        lemma: 'مصري',
        posCat: 'اسم',
        pos: 'نسبة',
        language: 'فصحى',
        confidence: 0.85,
    );

    $builder->add(
        source: 'manual',
        form: 'مصري',
        root: 'مصر',
        lemma: 'مصر',
        posCat: 'اسم',
        pos: 'نسبة',
        language: 'فصحى',
        confidence: 0.98,
    );

    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ))->analyze('مصري', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('مصر')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('manual_lexicon')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
});
