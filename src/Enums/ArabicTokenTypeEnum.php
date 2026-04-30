<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum ArabicTokenTypeEnum: string
{
    case Empty = 'empty';
    case Punctuation = 'punctuation';
    case Number = 'number';
    case SingleLetterParticle = 'single_letter_particle';
    case Particle = 'particle';
    case DefiniteArticle = 'definite_article';
    case ShortUnknown = 'short_unknown';
    case Word = 'word';
}
