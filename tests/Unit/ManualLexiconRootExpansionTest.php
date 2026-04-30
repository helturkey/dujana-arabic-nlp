<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('expands broken plural roots through manual sqlite lexicon', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-manual-root-expansion-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    foreach ([
        'بحور' => 'بحر',
        'أقلام' => 'قلم',
        'بيوت' => 'بيت',
        'أيام' => 'يوم',
    ] as $form => $expectedRoot) {
        $builder->add(
            source: 'manual',
            form: $form,
            root: $expectedRoot,
            lemma: $form,
            posCat: 'اسم',
            pos: 'اسم',
            language: 'فصحى حديثة',
            confidence: 0.98,
        );
    }

    $builder->write();

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    expect($analyzer->stem($word, StemmerModeEnum::Root))->toBe($root);
})->with([
    ['بحور', 'بحر'],
    ['أقلام', 'قلم'],
    ['بيوت', 'بيت'],
    ['أيام', 'يوم'],
]);
