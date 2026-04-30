<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Enums;

enum HamzaStrategyEnum: string
{
    /**
     * Keep hamza seats unchanged:
     * أ، إ، آ، ؤ، ئ remain as they are.
     */
    case PreserveSeat = 'preserve_seat';

    /**
     * Search-friendly:
     * أ إ آ ٱ => ا
     * ؤ => و
     * ئ => ي
     */
    case Search = 'search';

    /**
     * Morphology-aware conservative:
     * أ إ آ ٱ => ا
     * ؤ and ئ are preserved because they may carry root evidence.
     */
    case Morphology = 'morphology';

    /**
     * Aggressive root experiment:
     * أ إ آ ٱ ؤ ئ => ء
     */
    case UnifyHamza = 'unify_hamza';
}
