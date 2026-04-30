<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Laravel\Facades\DujanaArabicNlp;

it('resolves analyzer and facade from laravel container', function (): void {
    expect(app(ArabicAnalyzer::class)->stem('وكتابهم'))->toBe('كتاب')
        ->and(DujanaArabicNlp::stem('وكتابهم'))->toBe('كتاب');
});
