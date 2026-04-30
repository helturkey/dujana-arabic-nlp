<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Laravel\Facades;

use Dujana\ArabicNlp\ArabicAnalysis;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string normalize(string $word)
 * @method static array tokenize(string $text)
 * @method static string stem(string $word, ?StemmerModeEnum $mode = null)
 * @method static ArabicAnalysis analyze(string $word, ?StemmerModeEnum $mode = null)
 * @method static array stemSentence(string $sentence, ?StemmerModeEnum $mode = null)
 * @method static string stemSentenceAsString(string $sentence, ?StemmerModeEnum $mode = null)
 */
final class DujanaArabicNlp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dujana-arabic-nlp';
    }
}
