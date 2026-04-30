<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum GuardReasonEnum: string
{
    case Empty = 'empty';
    case TooShort = 'too_short';
    case StopWord = 'stop_word';
    case ProperName = 'proper_name';
    case NonStemmable = 'non_stemmable';

    case Punctuation = 'punctuation';
    case Number = 'number';
    case SingleLetterParticle = 'single_letter_particle';
    case Particle = 'particle';
    case DefiniteArticle = 'definite_article';
    case ShortUnknown = 'short_unknown';
}
