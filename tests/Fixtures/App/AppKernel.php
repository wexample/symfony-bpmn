<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App;

use Wexample\SymfonyBpmn\WexampleSymfonyBpmnBundle;
use Wexample\SymfonyTesting\Tests\Fixtures\AbstractFixtureKernel;

class AppKernel extends AbstractFixtureKernel
{
    protected function getFixtureDir(): string
    {
        return __DIR__;
    }

    protected function getExtraBundles(): iterable
    {
        return [
            new WexampleSymfonyBpmnBundle(),
        ];
    }

    protected function configuresItsOwnSecurity(): bool
    {
        return true;
    }

    protected function getConfigFiles(): array
    {
        return [__DIR__ . '/config/config.yaml'];
    }
}
