<?php

namespace Micayael\CommandsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ProcessBuilder;

class TestsCommand extends Command
{
    private $phpunitBin;

    public function __construct(array $bundleConfig)
    {
        parent::__construct();

        if (isset($bundleConfig['code_tester'])) {
            $this->phpunitBin = $bundleConfig['code_tester']['phpunit_bin'];
        }
    }

    protected function configure()
    {
        $this->setName('app:test')
            ->setDescription('Execute unit tests using phpunit')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command execute unit tests using phpunit. 
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Ejecutando pruebas unitarias');

        if (!$this->phpunitBin) {
            $io->error('phpunit not configured');

            return 1;
        }

        $builder = new ProcessBuilder();

        $builder->setPrefix($this->phpunitBin);

        $process = $builder
            ->getProcess();

        $process->setTimeout(0);

        if ($output->isVerbose()) {
            $io->text('phpunit: '.$this->phpunitBin);
            $io->text('Ejecutando: '.str_replace("' '", ' ', $process->getCommandLine()));

            $io->newLine();
        }

        $process->run(
            function ($type, $buffer) use ($output) {
                $output->write($buffer);
            }
        );
    }
}
