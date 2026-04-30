<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookupRunner;

it('looks up lexicon entries through the lookup runner', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'dujana_lookup_').'.sqlite';

    $database = new LexiconDatabase($path);
    $builder = new LexiconBuilder($database);

    $builder->begin();

    $builder->add(
        source: 'manual',
        form: 'تنزف',
        root: 'نزف',
        lemma: 'نزف',
        posCat: 'verb',
        pos: 'فعل',
        language: 'fusha',
        confidence: 1.0,
    );

    $builder->write(clear: false);

    $result = (new LexiconLookupRunner)->lookup(
        word: 'تَنْزِفُ',
        databasePath: $path,
    );

    expect($result->results)->not->toBeEmpty()
        ->and($result->results[0]['entry']['root'])->toBe('نزف');

    @unlink($path);
});
