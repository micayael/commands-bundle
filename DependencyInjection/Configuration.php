<?php

namespace Micayael\CommandsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('micayael_commands');

        $rootNode
            ->children()
                ->arrayNode('options')
                    ->isRequired()
                    ->append($this->addProjectOptionsNode())
                    ->append($this->addVendorsOptionsNode())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function addProjectOptionsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('project');

        $node
            ->children()
                ->arrayNode('php')
                    ->requiresAtLeastOneElement()->isRequired()
                    ->prototype('array')
                        ->requiresAtLeastOneElement()->isRequired()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('views')
                    ->isRequired()
                    ->prototype('array')
                        ->requiresAtLeastOneElement()->isRequired()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('config')
                    ->isRequired()
                    ->prototype('array')
                        ->requiresAtLeastOneElement()->isRequired()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('styles')
                    ->isRequired()
                    ->prototype('array')
                        ->requiresAtLeastOneElement()->isRequired()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('scripts')
                    ->isRequired()
                    ->prototype('array')
                        ->requiresAtLeastOneElement()->isRequired()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->isRequired()
            ;

        return $node;
    }

    private function addVendorsOptionsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('vendors');

        $node
            ->children()
                ->arrayNode('php')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('views')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('config')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('styles')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
                ->arrayNode('scripts')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->isRequired()
        ;

        return $node;
    }
}
