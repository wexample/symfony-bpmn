<?php

namespace Wexample\SymfonyBpmn\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyBpmn\Interface\BpmnTaskHandlerInterface;
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

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
