<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('extracts selected mifaal instrument noun roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['مفتاح', 'فتح', 'rule:instrument_mf3al'],
    ['ميزان', 'وزن', 'rule:instrument_mf3al'],
    ['منشار', 'نشر', 'rule:instrument_mf3al'],
]);

it('treats selected mifala instrument nouns as lexical/manual until the rule is hardened', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-instrument-manual-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', $word, $root, $word, posCat: 'اسم', pos: 'اسم آلة', confidence: 0.98);
    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(lexiconDatabasePath: $path))
        ->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBeIn(['manual_lexicon', 'database']);
})->with([
    ['مكنسة', 'كنس'],
    ['مطرقة', 'طرق'],
    ['مبردة', 'برد'],
]);

test('it exposes mf3l instrument candidates without outranking place time', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe($root)
        ->and($sources)->toContain('rule:instrument_mf3l');
})->with([
    ['مبرد', 'برد'],
    ['مشرط', 'شرط'],
    ['منجل', 'نجل'],
]);

test('place time mf3l remains preferred over ambiguous instrument mf3l', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('مكتب', StemmerModeEnum::Root);

    $sources = collect($analysis->rootAnalysis?->candidates ?? [])
        ->map(fn ($candidate) => $candidate->source)
        ->all();

    expect($analysis->root)->toBe('كتب')
        ->and($analysis->rootAnalysis?->best?->source)->toBe('rule:place_time_mf3l')
        ->and($sources)->toContain('rule:instrument_mf3l');
});
