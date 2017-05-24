<?php

namespace Micayael\CommandsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PrecommitCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:precommit')
            ->setDescription('Execute "app:verify", "app:phpcs", "app:test" commands before commit changes')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command execute "app:verify", "app:phpcs", "app:test" 
                commands before commit changes.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $commands = array(
            'cache:clear' => array('--no-warmup' => true),
            'app:verify' => array(),
            'app:phpcs' => array(),
            'app:test' => array(),
        );

        foreach ($commands as $command => $arguments) {
            $command = $this->getApplication()->find($command);

            $arguments['command'] = $command;

            $input = new ArrayInput($arguments);

            $command->run($input, $output);
        }

        $io->success('Game Over!!');
    }
}
