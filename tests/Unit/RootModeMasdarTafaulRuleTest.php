<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts triliteral tafaul masdar roots when shadda is preserved', function (string $word, string $root): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);
    $sources = array_map(
        static fn ($candidate): string => $candidate->source,
        $analysis->rootAnalysis?->candidates ?? [],
    );

    // dump([
    //     'original' => $analysis->original,
    //     'normalized' => $analysis->normalized,
    //     'root' => $analysis->root,
    //     'sources' => $sources,
    // ]);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($sources)->toContain('rule:masdar_triliteral_tf33ul');
})->with([
    ['تعلُّم', 'علم'],
    ['تكبُّر', 'كبر'],
    ['تحسُّن', 'حسن'],
]);
