<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('keeps the historical fixture file readable', function (): void {
    $cases = require __DIR__.'/../Fixtures/root-cases.php';

    expect($cases)->toHaveKeys(['supported', 'known_failures'])
        ->and($cases['supported'])->toBeArray()
        ->and($cases['known_failures'])->toBeArray();
});

it('passes the current no-db systematic root contract', function (string $word, string $root): void {
    expect(ArabicAnalyzer::make()->stem($word, StemmerModeEnum::Root))->toBe($root);
})->with([
    ['يكتب', 'كتب'],
    ['يفتح', 'فتح'],
    ['استخرج', 'خرج'],
    ['تعلّم', 'علم'],
    ['انكسر', 'كسر'],
    ['مفتاح', 'فتح'],
]);
