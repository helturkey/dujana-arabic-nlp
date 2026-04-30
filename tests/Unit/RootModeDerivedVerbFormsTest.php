<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts selected derived verb form roots', function (string $word, string $root): void {
    expect(ArabicAnalyzer::make()->stem($word, StemmerModeEnum::Root))->toBe($root);
})->with([
    ['علّم', 'علم'],
    ['درّس', 'درس'],
    ['كلّم', 'كلم'],
    ['قدّم', 'قدم'],
    ['قاتل', 'قتل'],
    ['شارك', 'شرك'],
    ['جاهد', 'جهد'],
    ['سافر', 'سفر'],
    ['خاصم', 'خصم'],
    ['تعلّم', 'علم'],
    ['تدرّس', 'درس'],
    ['تقدّم', 'قدم'],
    ['تقاتل', 'قتل'],
    ['تشارك', 'شرك'],
    ['تجاهد', 'جهد'],
    ['تخاصم', 'خصم'],
]);
