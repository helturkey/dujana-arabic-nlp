<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Candidates\CoreCandidate;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Morphology\Root\RootCandidate;
use Dujana\ArabicNlp\Morphology\Root\RootCandidateRanker;
use Dujana\ArabicNlp\Morphology\Root\RootExtractor;

it('ranks root candidates by confidence and deduplicates by root and source', function (): void {
    $ranked = (new RootCandidateRanker)->rank([
        new RootCandidate('كتب', 0.40, 'scale'),
        new RootCandidate('كتب', 0.90, 'database'),
        new RootCandidate('كتب', 0.70, 'database'),
        new RootCandidate('كبت', 0.60, 'other'),
    ]);

    expect($ranked)->toHaveCount(3)
        ->and($ranked[0]->root)->toBe('كتب')
        ->and($ranked[0]->source)->toBe('database')
        ->and($ranked[0]->confidence)->toBe(0.90);

    $sources = array_map(
        static fn (RootCandidate $candidate): string => $candidate->root.'|'.$candidate->source,
        $ranked,
    );

    expect($sources)->toContain('كتب|database')
        ->and($sources)->toContain('كتب|scale')
        ->and($sources)->toContain('كبت|other');
});

it('returns root analysis object for a systematic rule candidate', function (): void {
    $analysis = RootExtractor::make()->extract(new CoreCandidate(
        originalSurface: 'يكتب',
        normalized: 'يكتب',
        core: 'يكتب',
    ));

    expect($analysis->word)->toBe('يكتب')
        ->and($analysis->best)->not->toBeNull()
        ->and($analysis->best->root)->toBe('كتب')
        ->and($analysis->best->source)->toBe('rule:verb_triliteral_yf3l')
        ->and($analysis->toArray())->toHaveKeys([
            'word',
            'root',
            'source',
            'confidence',
            'status',
            'reliable',
            'reason',
            'candidates_count',
            'candidates',
        ]);
});

it('passes documented systematic root cases without database', function (): void {
    foreach ([
        'يكتب' => 'كتب',
        'تكتب' => 'كتب',
        'استخرج' => 'خرج',
        'تعلّم' => 'علم',
        'انكسر' => 'كسر',
        'مفتاح' => 'فتح',
    ] as $word => $expectedRoot) {
        expect(ArabicAnalyzer::make()->stem($word, StemmerModeEnum::Root))
            ->toBe($expectedRoot, "Failed root for [{$word}]");
    }
});

it('documents known root fixture failures separately', function (): void {
    $cases = require __DIR__.'/../Fixtures/root-cases.php';

    expect($cases)->toHaveKey('known_failures')
        ->and($cases['known_failures'])->toBeArray();
});
