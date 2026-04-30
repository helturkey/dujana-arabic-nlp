<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('extracts quadriliteral roots without making bare forms authoritative', function (string $word, string $root, string $source, bool $reliable): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBe($reliable)
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['دحرج', 'دحرج', 'rule:verb_quadriliteral_f3ll', false],
    ['زلزل', 'زلزل', 'rule:verb_quadriliteral_f3ll', false],
    ['وسوس', 'وسوس', 'rule:verb_quadriliteral_f3ll', false],

    ['دحرجة', 'دحرج', 'rule:masdar_quadriliteral_f3lla', true],
    ['زلزلة', 'زلزل', 'rule:masdar_quadriliteral_f3lla', true],
    ['وسوسة', 'وسوس', 'rule:masdar_quadriliteral_f3lla', true],

    ['زلزال', 'زلزل', 'rule:masdar_quadriliteral_f3lal', true],
]);

it('lets database win over a systematic rule when database has a stronger trusted entry', function (): void {
    $path = sys_get_temp_dir().'/dujana-db-first-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add(
        source: 'manual',
        form: 'استخرج',
        root: 'خرج',
        lemma: 'استخرج',
        posCat: 'فعل',
        pos: 'فعل',
        language: 'فصحى',
        confidence: 0.98,
    );

    $builder->write(clear: false);

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    $analysis = $analyzer->analyze('استخرج', StemmerModeEnum::Root);

    expect($analysis->root)->toBe('خرج')
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
});

it('extracts ifalla color and defect pattern roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['احمرّ', 'حمر', 'rule:verb_if3ll'],
    ['اخضرّ', 'خضر', 'rule:verb_if3ll'],
    ['اصفرّ', 'صفر', 'rule:verb_if3ll'],
    ['احمرار', 'حمر', 'rule:masdar_quinqueliteral_if3lal'],
    ['اخضرار', 'خضر', 'rule:masdar_quinqueliteral_if3lal'],
    ['اصفرار', 'صفر', 'rule:masdar_quinqueliteral_if3lal'],
]);

it('extracts quadriliteral roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['دحرج', 'دحرج', 'rule:verb_quadriliteral_f3ll'],
    ['زلزل', 'زلزل', 'rule:verb_quadriliteral_f3ll'],
    ['وسوس', 'وسوس', 'rule:verb_quadriliteral_f3ll'],

    ['دحرجة', 'دحرج', 'rule:masdar_quadriliteral_f3lla'],
    ['زلزلة', 'زلزل', 'rule:masdar_quadriliteral_f3lla'],
    ['وسوسة', 'وسوس', 'rule:masdar_quadriliteral_f3lla'],

    ['زلزال', 'زلزل', 'rule:masdar_quadriliteral_f3lal'],
]);

it('extracts augmented quadriliteral tafaalala roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_quadriliteral_tf3ll');
})->with([
    ['تدحرج', 'دحرج'],
    ['تزلزل', 'زلزل'],
    ['توسوس', 'وسوس'],
]);

it('extracts present triliteral strong verb roots without database', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('rule:verb_triliteral_yf3l');
})->with([
    ['يكتب', 'كتب'],
    ['تكتب', 'كتب'],
    ['نكتب', 'كتب'],
    ['يفتح', 'فتح'],
    ['يكسر', 'كسر'],
]);
