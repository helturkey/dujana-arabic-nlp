<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('extracts visible geminated doubled roots by rule without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source)
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['مدّ', 'مدد', 'rule:verb_triliteral_f3ll'],
    ['شدّ', 'شدد', 'rule:verb_triliteral_f3ll'],
    ['ردّ', 'ردد', 'rule:verb_triliteral_f3ll'],
    ['عدّ', 'عدد', 'rule:verb_triliteral_f3ll'],
    ['فرّ', 'فرر', 'rule:verb_triliteral_f3ll'],
    ['يمدّ', 'مدد', 'rule:verb_triliteral_yf3ll'],
    ['يشدّ', 'شدد', 'rule:verb_triliteral_yf3ll'],
]);

it('keeps unvocalized short doubled forms lexical/manual, not rule-only', function (string $word): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->best?->source)->not->toBe('rule:verb_triliteral_f3ll');
})->with(['مد', 'شد', 'رد', 'عد', 'فر', 'مر', 'حل', 'ضم', 'سر']);

it('resolves unvocalized short doubled forms through manual database only', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-doubled-roots-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    foreach ([
        ['مد', 'مدد'],
        ['شد', 'شدد'],
        ['رد', 'ردد'],
    ] as [$form, $manualRoot]) {
        $builder->add(
            source: 'manual',
            form: $form,
            root: $manualRoot,
            lemma: $form,
            posCat: 'فعل',
            pos: 'فعل',
            language: 'فصحى',
            confidence: 0.98,
        );
    }

    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ))->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['مد', 'مدد'],
    ['شد', 'شدد'],
    ['رد', 'ردد'],
]);
