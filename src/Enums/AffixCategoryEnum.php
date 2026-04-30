<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum AffixCategoryEnum: string
{
    case Unknown = 'unknown';
    case DefiniteArticle = 'definite_article';
    case Conjunction = 'conjunction';
    case Preposition = 'preposition';
    case ConjunctionDefiniteArticle = 'conjunction_definite_article';
    case PrepositionDefiniteArticle = 'preposition_definite_article';
    case ConjunctionPreposition = 'conjunction_preposition';
    case ConjunctionPrepositionDefiniteArticle = 'conjunction_preposition_definite_article';
    case Pronoun = 'pronoun';
    case Plural = 'plural';
    case Dual = 'dual';
    case Feminine = 'feminine';
    case Nisba = 'nisba';
    case FinalAlef = 'final_alef';
    case FinalWeak = 'final_weak';
    case VerbSuffix = 'verb_suffix';
}
