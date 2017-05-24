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
                ->arrayNode('search_in_code')
                    ->isRequired()
                    ->append($this->addProjectOptionsNode())
                    ->append($this->addVendorsOptionsNode())
                ->end()
                ->arrayNode('verify')
                    ->append($this->addVerifyPatternsNode())
                ->end()
                ->arrayNode('code_formatter')
                    ->children()
                        ->scalarNode('phpcsfixer_bin')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('code_tester')
                    ->children()
                        ->scalarNode('phpunit_bin')
                            ->isRequired()
                        ->end()
                    ->end()
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
            ->info('File extension list and folders to know where to look for inside the project')
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
            ->info('File extension list and folders to know where to look for inside the vendors')
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

    private function addVerifyPatternsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('patterns');

        $node
            ->info('This patterns will be looked for using folders defined in <info>search_in_code</info> command config')
            ->children()
                ->arrayNode('php')
                    ->prototype('scalar')->end()
                    ->info('Patterns to look for in php files')
                ->end()
                ->arrayNode('views')
                    ->prototype('scalar')->end()
                    ->info('Patterns to look for in view files (twig)')
                ->end()
                ->arrayNode('config')
                    ->prototype('scalar')->end()
                    ->info('Patterns to look for in config files (yml)')
                ->end()
                ->arrayNode('styles')
                    ->prototype('scalar')->end()
                    ->info('Patterns to look for in style files (css, sass)')
                ->end()
                ->arrayNode('scripts')
                    ->prototype('scalar')->end()
                    ->info('Patterns to look for in script files (js)')
                ->end()
            ->end()
        ;

        return $node;
    }
}
