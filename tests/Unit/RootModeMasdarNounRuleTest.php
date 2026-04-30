<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('extracts selected masdar noun roots without database', function (string $word, string $root, string $source): void {
    $analysis = ArabicAnalyzer::make()->analyze($word, StemmerModeEnum::Root);

    expect($analysis->root)->toBe($root)
        ->and($analysis->rootAnalysis)->not->toBeNull()
        ->and($analysis->rootAnalysis->best)->not->toBeNull()
        ->and($analysis->rootAnalysis->best->source)->toBe($source);
})->with([
    ['كتابة', 'كتب', 'rule:masdar_triliteral_f3ala'],

    ['تعليم', 'علم', 'rule:masdar_triliteral_tf3eel'],
    ['تدريس', 'درس', 'rule:masdar_triliteral_tf3eel'],
    ['تكبير', 'كبر', 'rule:masdar_triliteral_tf3eel'],

    ['استخراج', 'خرج', 'rule:masdar_sextiliteral_istf3al'],
    ['استعمال', 'عمل', 'rule:masdar_sextiliteral_istf3al'],
    ['استقبال', 'قبل', 'rule:masdar_sextiliteral_istf3al'],

    ['اجتماع', 'جمع', 'rule:masdar_quinqueliteral_ift3al'],
    ['اختلاف', 'خلف', 'rule:masdar_quinqueliteral_ift3al'],
    ['اقتراب', 'قرب', 'rule:masdar_quinqueliteral_ift3al'],

    ['انكسار', 'كسر', 'rule:masdar_quinqueliteral_inf3al'],
    ['انفتاح', 'فتح', 'rule:masdar_quinqueliteral_inf3al'],
    ['انقطاع', 'قطع', 'rule:masdar_quinqueliteral_inf3al'],
]);
