<?php

declare(strict_types=1);

namespace Dujana\ArabicNlp\Tests;

use Dujana\ArabicNlp\Laravel\DujanaArabicNlpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DujanaArabicNlpServiceProvider::class,
        ];
    }
}
