<?php

namespace Micayael\CommandsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class VerifyCodeCommand extends Command
{
    private $patterns = [];

    public function __construct(array $bundleConfig)
    {
        parent::__construct();

        if(isset($bundleConfig['verify'])) {
            $this->patterns = $bundleConfig['verify']['patterns'];
        }
    }

    protected function configure()
    {
        $this->setName('app:verify')
            ->setDescription('Look for common errors in the code within the project')
            ->setHelp(
                <<<EOF
El comando <info>%command.name%</info> looks for within the project certain test codes that usually are forgotten by 
developers in order to be removed [dump(), die(), echo, print_r(), var_dump(), ->debug()].
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $command = $this->getApplication()->find('app:search');

        if(empty($this->patterns)){
            $io->error('Este comando no se encuentra configurado.');

            return;
        }

        foreach ($this->patterns as $option => $patterns) {

            if(!empty($patterns)) {

                $input = new ArrayInput(
                    array(
                        'command' => 'app:search',
                        'patterns' => $patterns,
                        '--' . $option => true,
                    )
                );

                $command->run($input, $output);
            }
        }
    }
}
