<?php
declare(strict_types = 1);

require 'vendor/autoload.php';

use Innmind\BlackBox\{
    Application,
    Runner\CodeCoverage,
    PHPUnit\Load,
};

Application::new($argv)
    ->when(
        \getenv('ENABLE_COVERAGE') !== false,
        static fn(Application $app) => $app
            ->codeCoverage(
                CodeCoverage::of(
                    __DIR__.'/src/',
                    __DIR__.'/tests/',
                )
                    ->dumpTo('coverage.clover')
                    ->enableWhen(true),
            )
            ->scenariiPerProof(1),
    )
    ->tryToProve(Load::directory(__DIR__.'/tests'))
    ->exit();
