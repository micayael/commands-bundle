<?php

namespace Micayael\CommandsBundle\DependencyInjection;

use Micayael\CommandsBundle\Command\SearchInCodeCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class MicayaelCommandsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['search_in_code'])) {
            $searchInCodeCommand = $container->register('micayael_commands.command.search_in_code', SearchInCodeCommand::class);

            $searchInCodeCommand->setArguments([
                new Reference('translator'),
                $config['search_in_code'],
            ]);

            $searchInCodeCommand->addTag('console.command');
        }
    }
}
