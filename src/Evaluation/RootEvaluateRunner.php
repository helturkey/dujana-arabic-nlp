<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Evaluation;

use Dujana\ArabicNlp\ArabicAnalyzer;
use Dujana\ArabicNlp\Config\ArabicNlpConfig;
use Dujana\ArabicNlp\Enums\StemmerModeEnum;

final class RootEvaluateRunner
{
    public function evaluate(
        string $word,
        string $expectedRoot,
        ?string $databasePath = null,
    ): RootEvaluateResult {
        $analyzer = ArabicAnalyzer::make(new ArabicNlpConfig(
            lexiconDatabasePath: $databasePath,
        ));

        $analysis = $analyzer->analyze($word, StemmerModeEnum::Root);

        return new RootEvaluateResult(
            word: $word,
            expectedRoot: $expectedRoot,
            analysis: $analysis,
            passed: $analysis->root === $expectedRoot,
        );
    }
}
