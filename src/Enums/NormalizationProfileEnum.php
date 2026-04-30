<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum NormalizationProfileEnum: string
{
    case Raw = 'raw';

    case Search = 'search';

    case Stemming = 'stemming';

    case Morphology = 'morphology';
}
