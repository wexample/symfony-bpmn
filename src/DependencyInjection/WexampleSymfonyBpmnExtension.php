<?php

namespace Wexample\SymfonyBpmn\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyActivity\WexampleSymfonyActivityBundle;
use Wexample\SymfonyBpmn\EventSubscriber\BpmnActivitySubscriber;
use Wexample\SymfonyBpmn\EventSubscriber\BpmnNotificationSubscriber;
use Wexample\SymfonyBpmn\Interface\BpmnRecipientResolverInterface;
use Wexample\SymfonyBpmn\Interface\BpmnTaskHandlerInterface;
use Wexample\SymfonyNotification\WexampleSymfonyNotificationBundle;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class WexampleSymfonyBpmnExtension extends AbstractWexampleSymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('wexample_symfony_bpmn.paths', $config['paths']);
        $container->setParameter('wexample_symfony_bpmn.lanes', $config['lanes']);

        $container
            ->registerForAutoconfiguration(BpmnTaskHandlerInterface::class)
            ->addTag(BpmnTaskHandlerInterface::TAG);
        $container
            ->registerForAutoconfiguration(BpmnRecipientResolverInterface::class)
            ->addTag(BpmnRecipientResolverInterface::TAG);

        $this->loadConfig(
            __DIR__,
            $container
        );

        // The history of each subject, and the notifications of its
        // messages, where the application installed what keeps and sends
        // them.
        $bundles = $container->getParameter('kernel.bundles');

        if (class_exists(WexampleSymfonyActivityBundle::class) && in_array(WexampleSymfonyActivityBundle::class, $bundles, true)) {
            $container->autowire(BpmnActivitySubscriber::class)->setAutoconfigured(true);
        }

        if (class_exists(WexampleSymfonyNotificationBundle::class) && in_array(WexampleSymfonyNotificationBundle::class, $bundles, true)) {
            $container->autowire(BpmnNotificationSubscriber::class)->setAutoconfigured(true);
        }
    }
}
