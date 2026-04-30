<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('allows trusted manual database entries to resolve short ambiguous roots', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-short-manual-root-'.uniqid().'.sqlite';

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
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe('manual_lexicon')
        ->and($analysis->rootAnalysis->reliable())->toBeTrue();
})->with([
    ['مد', 'مدد'],
    ['شد', 'شدد'],
    ['رد', 'ردد'],
]);

it('does not allow non-manual database entries to resolve short ambiguous roots', function (): void {
    $path = sys_get_temp_dir().'/dujana-short-non-manual-root-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    $builder->add(
        source: 'arramooz',
        form: 'مد',
        root: 'مدد',
        lemma: 'مد',
        posCat: 'فعل',
        pos: 'فعل',
        language: 'فصحى',
        confidence: 0.99,
    );

    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ))->analyze('مد', StemmerModeEnum::Root);

    expect($analysis->rootAnalysis?->best?->source)->not->toBe('manual_lexicon')
        ->and($analysis->rootAnalysis?->reliable() ?? false)->toBeFalse();
});

it('keeps database alternatives non-authoritative', function (): void {
    $path = sys_get_temp_dir().'/dujana-database-alternatives-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);

    /*
     * Same form, conflicting roots.
     * Builder / database layer should treat non-best roots as alternatives.
     */
    $builder->add(
        source: 'arramooz',
        form: 'بار',
        root: 'بور',
        lemma: 'بار',
        posCat: 'فعل',
        pos: 'فعل',
        language: 'فصحى',
        confidence: 0.85,
    );

    $builder->add(
        source: 'arramooz',
        form: 'بار',
        root: 'بءر',
        lemma: 'بار',
        posCat: 'فعل',
        pos: 'فعل',
        language: 'فصحى',
        confidence: 0.80,
    );

    $builder->add(
        source: 'arramooz',
        form: 'بار',
        root: 'بير',
        lemma: 'بار',
        posCat: 'فعل',
        pos: 'فعل',
        language: 'فصحى',
        confidence: 0.78,
    );

    $builder->write(clear: false);

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ))->analyze('بار', StemmerModeEnum::Root);

    $alternativeCandidates = array_values(array_filter(
        $analysis->rootAnalysis?->candidates ?? [],
        static fn ($candidate): bool => $candidate->source === 'database_alternative'
    ));

    expect($alternativeCandidates)->not->toBeEmpty();

    foreach ($alternativeCandidates as $candidate) {
        expect($candidate->isAuthoritative())->toBeFalse();
    }
});
