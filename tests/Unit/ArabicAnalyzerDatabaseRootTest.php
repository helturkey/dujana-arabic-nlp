<?php

declare(strict_types=1);

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;

it('uses optional sqlite lexicon database in root mode', function (): void {
    $path = sys_get_temp_dir().'/dujana-analyzer-lexicon-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->add('manual', 'بحور', 'بحر', 'بحور', confidence: 0.98);
    $builder->write();

    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: $path,
    ));

    expect($analyzer->stem('بحور', StemmerModeEnum::Root))->toBe('بحر');
});

it('continues working when sqlite lexicon database is missing', function (): void {
    $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
        lexiconDatabasePath: '/missing/dujana-lexicon.sqlite',
    ));

    expect($analyzer->stem('كتاب', StemmerModeEnum::Root))->toBe('كتب');
});
