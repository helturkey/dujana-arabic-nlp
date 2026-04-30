<?php

declare(strict_types=1);

it('keeps classification protection policy inside StemGuard, not ArabicAnalyzer', function (): void {
    $analyzer = (string) file_get_contents(__DIR__.'/../../src/ArabicAnalyzer.php');
    $guard = (string) file_get_contents(__DIR__.'/../../src/Guards/StemGuard.php');

    expect($analyzer)
        ->not->toContain('shouldProtectByClassification')
        ->not->toContain('ArabicTokenTypeEnum::ShortUnknown')
        ->not->toContain('$classification->protected &&')
        ->not->toContain('if ($classification->protected');

    expect($guard)
        ->toContain('ArabicTokenTypeEnum::ShortUnknown')
        ->toContain('GuardReasonEnum::tryFrom')
        ->toContain('$classification->type->value');
});
