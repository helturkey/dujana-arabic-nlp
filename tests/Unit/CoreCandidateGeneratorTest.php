<?php

use Dujana\ArabicNlp\Candidates\CoreCandidateGenerator;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('keeps original surface while generating morphology-normalized candidates', function (): void {
    $generator = new CoreCandidateGenerator;

    $candidates = $generator->generate(
        word: 'تعلّم',
        mode: StemmerModeEnum::Root,
        originalSurface: 'تعلُّم',
    );

    expect($candidates)->not->toBeEmpty()
        ->and($candidates[0]->originalSurface)->toBe('تعلُّم')
        ->and($candidates[0]->normalized)->toBe('تعلّم')
        ->and($candidates[0]->core)->toBe('تعلّم');
});
