<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('keeps unvalidated hamza reconstruction non-reliable without legacy extractor', function (): void {
    $analysis = ArabicAnalyzer::make()->analyze('يبدأ', StemmerModeEnum::Root);

    expect($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBeFalse();

    expect($analysis->rootAnalysis?->reliable() ?? false)->toBeFalse();
});

it('resolves hamza verb forms reliably when validated by lexicon', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-hamza-reconstruction-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    foreach ([
        ['قرأ', 'قرأ'],
        ['قرأوا', 'قرأ'],
        ['يقرأ', 'قرأ'],
        ['يقرا', 'قرأ'],
        ['بدأ', 'بدأ'],
        ['يبدأ', 'بدأ'],
        ['يبدا', 'بدأ'],
        ['أخذ', 'أخذ'],
        ['يأخذ', 'أخذ'],
        ['ياخذ', 'أخذ'],
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

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    $analysis = $analyzer->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();

    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis->candidates,
    );

    expect(array_intersect($sources, ['manual_lexicon', 'database']))->not->toBeEmpty();
})->with([
    ['قرأوا', 'قرأ'],
    ['يقرأ', 'قرأ'],
    ['يبدأ', 'بدأ'],
    ['يأخذ', 'أخذ'],
]);
