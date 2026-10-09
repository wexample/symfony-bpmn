<?php

namespace Wexample\SymfonyBpmn\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_bpmn');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('paths')
                    ->info('The directories holding the `.bpmn` files of the application, every process of each file run by its id.')
                    ->scalarPrototype()->end()
                    ->defaultValue(['%kernel.project_dir%/config/bpmn'])
                ->end()
                ->scalarNode('view_role')
                    ->info('Role needed to read the processes through the API — their diagram, paths, rights and validation.')
                    ->defaultValue('ROLE_USER')
                ->end()
                ->arrayNode('lanes')
                    ->info('Who does the tasks of a lane, by process then lane: `role` held, or `grant` held on the group of the subject. Over what the lane says itself (`wex:property` role or grant).')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('process')
                    ->arrayPrototype()
                        ->normalizeKeys(false)
                        ->useAttributeAsKey('lane')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('role')->defaultNull()->end()
                                ->scalarNode('grant')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
