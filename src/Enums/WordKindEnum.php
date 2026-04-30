<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum WordKindEnum: string
{
    case Unknown = 'unknown';
    case Noun = 'noun';
    case Verb = 'verb';
    case Particle = 'particle';
    case ProperName = 'proper_name';
}
