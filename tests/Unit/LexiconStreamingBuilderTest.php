<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Lexicon\Database\LexiconBuilder;
use Dujana\ArabicNlp\Lexicon\Database\LexiconDatabase;
use Dujana\ArabicNlp\Lexicon\Database\LexiconLookup;

it('supports streaming append builds without clearing existing entries', function (): void {
    $path = sys_get_temp_dir().'/dujana-streaming-append-'.uniqid().'.sqlite';

    $database = new LexiconDatabase($path);

    $builder = new LexiconBuilder($database);
    $builder->begin(clear: true);
    $builder->add('manual', 'بحور', 'بحر', confidence: 0.98);
    expect($builder->write(clear: false))->toBe(1);

    $builder = new LexiconBuilder($database);
    $builder->begin(clear: false);
    $builder->add('manual', 'أقلام', 'قلم', confidence: 0.98);
    expect($builder->write(clear: false))->toBe(2);

    $lookup = new LexiconLookup($database);

    expect($lookup->lookup('بحور')[0]->root)->toBe('بحر')
        ->and($lookup->lookup('اقلام')[0]->root)->toBe('قلم');
});
