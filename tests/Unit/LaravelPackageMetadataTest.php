<?php

declare(strict_types=1);

use Dujana\ArabicNlp\Console\BuildLexiconCommand;
use Dujana\ArabicNlp\Console\LexiconLookupCommand;
use Dujana\ArabicNlp\Console\LexiconStatsCommand;
use Dujana\ArabicNlp\Laravel\DujanaArabicNlpServiceProvider;

it('exposes laravel service provider and commands', function (): void {
    expect(class_exists(DujanaArabicNlpServiceProvider::class))->toBeTrue()
        ->and(class_exists(BuildLexiconCommand::class))->toBeTrue()
        ->and(class_exists(LexiconStatsCommand::class))->toBeTrue()
        ->and(class_exists(LexiconLookupCommand::class))->toBeTrue();
});
