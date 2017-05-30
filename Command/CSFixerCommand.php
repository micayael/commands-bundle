<?php

namespace Micayael\CommandsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ProcessBuilder;

class CSFixerCommand extends Command
{
    private $phpcsfixerBin;

    public function __construct(array $bundleConfig)
    {
        parent::__construct();

        if (isset($bundleConfig['code_formatter'])) {
            $this->phpcsfixerBin = $bundleConfig['code_formatter']['phpcsfixer_bin'];
        }
    }

    protected function configure()
    {
        $this->setName('app:phpcs')
            ->setDescription('Format code using php-cs-fixer')
            ->setHelp(
                <<<EOF
                The <info>%command.name%</info> command execute php-cs-fixer.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Reformateando código');

        if (!$this->phpcsfixerBin) {
            $io->error('php-cs-fixer not configured');

            return 1;
        }

        $builder = new ProcessBuilder();

        $builder->setPrefix($this->phpcsfixerBin);

        $process = $builder
            ->setArguments(array('fix', '-vv'))
            ->getProcess();

        $process->setTimeout(0);

        if ($output->isVerbose()) {
            $io->text('php-cs-fixer: '.$this->phpcsfixerBin);
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
