<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('exposes database root alternatives in root analysis', function (): void {
    $path = sys_get_temp_dir().'/dujana-root-alternatives-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->add(
        source: 'qabas',
        form: 'أبواب',
        root: 'باب',
        lemma: 'أبواب',
        confidence: 0.94,
    );

    $builder->add(
        source: 'manual',
        form: 'ابواب',
        root: 'بوب',
        lemma: 'ابواب',
        confidence: 0.98,
    );

    $builder->write();

    $analysis = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ))->analyze('أبواب', StemmerModeEnum::Root);

    expect($analysis->rootAnalysis)->not->toBeNull();

    $array = $analysis->toArray();

    expect($array['root_analysis']['candidates'])->toBeArray();

    $roots = array_column($array['root_analysis']['candidates'], 'root');

    expect($roots)->toContain('باب')
        ->and($roots)->toContain('بوب');
});
