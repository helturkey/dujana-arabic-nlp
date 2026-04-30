<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('expands larger broken plural coverage through manual sqlite lexicon', function (string $word, string $root): void {
    $path = sys_get_temp_dir().'/dujana-larger-broken-plurals-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    foreach ([
        'ليال' => 'ليل',
        'نفوس' => 'نفس',
        'عيون' => 'عين',
        'أفعال' => 'فعل',
        'أقوال' => 'قول',
        'أعمال' => 'عمل',
        'أسماء' => 'اسم',
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
    ['ليال', 'ليل'],
    ['نفوس', 'نفس'],
    ['عيون', 'عين'],
    ['أفعال', 'فعل'],
    ['أقوال', 'قول'],
    ['أعمال', 'عمل'],
    ['أسماء', 'اسم'],
]);
