<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum ChangeLocationEnum: string
{
    case ExternalPrefix = 'external_prefix';
    case ExternalSuffix = 'external_suffix';
    case InternalPattern = 'internal_pattern';
    case FinalChange = 'final_change';
}
