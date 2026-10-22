<?php

namespace Wexample\SymfonyBpmn\Tests\Fixtures\App;

use DH\AuditorBundle\DHAuditorBundle;
use Wexample\SymfonyActivity\WexampleSymfonyActivityBundle;
use Wexample\SymfonyLoader\WexampleSymfonyLoaderBundle;
use Wexample\SymfonyMail\WexampleSymfonyMailBundle;
use Wexample\SymfonyNotification\WexampleSymfonyNotificationBundle;
use Wexample\SymfonySecurity\WexampleSymfonySecurityBundle;
use Wexample\SymfonyTranslations\WexampleSymfonyTranslationsBundle;

/**
 * Bpmn with what follows it when installed: the history of
 * symfony-activity, the notifications of symfony-notification.
 */
class IntegratedAppKernel extends AppKernel
{
    protected function getExtraBundles(): iterable
    {
        return [
            new DHAuditorBundle(),
            new WexampleSymfonySecurityBundle(),
            new WexampleSymfonyLoaderBundle(),
            new WexampleSymfonyTranslationsBundle(),
            new WexampleSymfonyMailBundle(),
            new WexampleSymfonyActivityBundle(),
            new WexampleSymfonyNotificationBundle(),
            ...parent::getExtraBundles(),
        ];
    }

    public function getCacheDir(): string
    {
        return parent::getCacheDir() . '/integrated';
    }

    protected function getConfigFiles(): array
    {
        return [...parent::getConfigFiles(), __DIR__ . '/config/integrated.yaml'];
    }
}
