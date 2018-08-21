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

                ->scalarNode('locale')
                    ->defaultValue('en')
                ->end()

                ->arrayNode('search_in_code')

                        ->children()
                            ->scalarNode('default_option')
                                ->defaultValue('php')
                            ->end()
                        ->end()

                        ->append($this->getSearchInCodeAppOptionsNode())
                        ->append($this->getSearchInCodeVendorsOptionsNode())

                ->end()
            ->end();

        return $treeBuilder;
    }

    private function getSearchInCodeAppOptionsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('app');

        $node

            ->defaultValue([
                'php' => [
                    'php' => ['src'],
                ],
            ])

            ->arrayPrototype() //views

                ->arrayPrototype() // twig

                    ->scalarPrototype()->end() // app/Resources/views

                ->end()

            ->end();

        return $node;
    }

    private function getSearchInCodeVendorsOptionsNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('vendors');

        $node

            ->arrayPrototype() //views

                ->arrayPrototype() // twig

                    ->scalarPrototype()->end() // app/Resources/views

                ->end()

            ->end();

        return $node;
    }
}
