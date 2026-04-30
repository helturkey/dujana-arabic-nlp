<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

it('protects stop words and proper names', function (string $word, string $reason): void {
    $analysis = ArabicAnalyzer::make()->analyze($word);

    expect($analysis->protected)->toBeTrue()
        ->and($analysis->protectionReason)->toBe($reason)
        ->and($analysis->stem)->toBe($analysis->normalized);
})->with([
    ['من', 'particle'],
    ['محمد', 'proper_name'],
    ['احمد', 'proper_name'],
]);

it('uses external lexicon files when configured', function (): void {
    $dir = sys_get_temp_dir().'/dujana-lexicon-'.uniqid();
    mkdir($dir, 0777, true);

    $stop = $dir.'/stopwords.txt';
    $names = $dir.'/names.txt';
    $frozen = $dir.'/frozen.txt';

    file_put_contents($stop, "حيثما\n");
    file_put_contents($names, "زيدان\n");
    file_put_contents($frozen, "هيهات\n");

    $config = new ArabicNlpConfig(
        stopWordsPath: $stop,
        properNamesPath: $names,
        nonStemmablePath: $frozen,
    );

    $analyzer = ArabicAnalyzer::make($config);

    expect($analyzer->analyze('حيثما')->protectionReason)->toBe('stop_word')
        ->and($analyzer->analyze('زيدان')->protectionReason)->toBe('proper_name')
        ->and($analyzer->analyze('هيهات')->protectionReason)->toBe('non_stemmable');
});

it('lets root mode analyze short doubled words while moderate mode protects them', function (): void {
    $analyzer = ArabicAnalyzer::make();

    $rootAnalysis = $analyzer->analyze('مد', StemmerModeEnum::Root)->rootAnalysis;

    expect($analyzer->analyze('مد')->protected)->toBeTrue()
        ->and($rootAnalysis?->reliable() ?? false)->toBeFalse();
});
