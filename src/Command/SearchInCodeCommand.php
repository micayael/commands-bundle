<?php

namespace Micayael\CommandsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\TranslatorInterface;

class SearchInCodeCommand extends Command
{
    private $translator;

    /**
     * @var array
     */
    private $configs;

    /**
     * Luego de ejecutarse el método processOptions() contiene las opciones a ser buscadas de acuerdo a las opciones
     * solicitadas el ejecutar el comando.
     *
     * @see $directories
     *
     * @var array
     */
    private $options = [];

    /**
     * Luego de ejecutarse el método processOptions() contiene los directorios en donde se realizarán las búsquedas
     * de acuerdo a las opciones solicitadas el ejecutar el comando.
     *
     * @see $options
     *
     * @var array
     */
    private $directories = [];

    public function __construct(TranslatorInterface $translator, array $configs)
    {
        $this->translator = $translator;
        $this->configs = $configs;

        parent::__construct(null);
    }

    protected function configure()
    {
        $wordWrapSize = $this->getWordWrapDefault();

        $this
            ->setName('app:search')
            ->setDescription($this->translator->trans('search_in_code.description'))
            ->setHelp($this->translator->trans('search_in_code.help'))
            ->addArgument(
                'patterns',
                InputArgument::IS_ARRAY,
                $this->translator->trans('search_in_code.arguments.patterns')
            )
            ->addOption(
                'i',
                '-i',
                InputOption::VALUE_NONE,
                $this->translator->trans('search_in_code.options.icase')
            )
            ->addOption(
                'word-wrap',
                'w',
                InputOption::VALUE_REQUIRED,
                $this->translator->trans('search_in_code.options.word_wrap'),
                $wordWrapSize
            )
            ->addOption(
                'csv',
                null,
                InputOption::VALUE_NONE,
                $this->translator->trans('search_in_code.options.csv')
            )
        ;

        if (!empty($this->configs)) {
            // Agrega las opciones configuradas para la app
            if (!empty($this->configs['app'])) {
                foreach ($this->configs['app'] as $option => $config) {
                    $this->addDefinedOption($option, $config);
                }
            }

            // Agrega las opciones configuradas para los vendors
            if (!empty($this->configs['vendors'])) {
                foreach ($this->configs['vendors'] as $option => $config) {
                    // Si la opción ya existe en las configuraciones de la app se salta ya que ya fue agregada
                    if (isset($this->configs['app'][$option])) {
                        continue;
                    }

                    $this->addDefinedOption($option, $config);
                }

                $this
                    ->addOption(
                        'include-vendors',
                        null,
                        InputOption::VALUE_NONE,
                        $this->translator->trans('search_in_code.options.include_vendors')
                    )
                    ->addOption(
                        'only-vendors',
                        null,
                        InputOption::VALUE_NONE,
                        $this->translator->trans('search_in_code.options.only_vendors')
                    );
            }

            // Agrega la opción all para buscar en todos los lugares
            $this->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                $this->translator->trans('search_in_code.options.all')
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $patterns = $input->getArgument('patterns');

        // Valida que se ingrese un pattern a buscar
        if (empty($patterns)) {
            $helper = $this->getHelper('question');

            $question = new Question($this->translator->trans('search_in_code.msgs.patterns_missing_question'));

            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                    throw new \RuntimeException(
                        $this->translator->trans('search_in_code.msgs.patterns_missing_error')
                    );
                }

                return $answer;
            });

            $question->setMaxAttempts(3);

            $answer = $helper->ask($input, $output, $question);

            $patterns = explode(' ', $answer);
        }

        $io->title($this->translator->trans('search_in_code.msgs.start'));

        $this->processOptions($this->configs, $input);

        $finder = $this->createFinder($input, $io, $patterns);

        $this->showResults($input, $output, $io, $finder, $patterns);

        return 0;
    }

    private function addDefinedOption(string $option, array $config): void
    {
        $fileExtensions = implode(',', array_keys($config));

        $msg = $this->translator->trans('search_in_code.options.option_x', ['%extensions%' => $fileExtensions]);

        if ($option == $this->configs['default_option']) {
            $msg .= ' <comment>(default)</comment>';
        }

        $this->addOption(
            $option,
            null,
            InputOption::VALUE_NONE,
            sprintf($msg, $fileExtensions)
        );
    }

    /**
     * Obtiene las configuraciones del bundle y las procesa teniendo en cuenta las opciones elegidas al ejecutar el
     * comando. Centraliza en los arrays $this->options, $this->directories los resultados finales.
     *
     * @param array          $configs
     * @param InputInterface $input
     */
    private function processOptions(array $configs, InputInterface $input): void
    {
        $vendorsOptionsConfigured = $input->hasOption('only-vendors') ? true : false;

        $noOptionApplied = true;

        // Verifica si no se ingresó ninguna opción de donde buscar para usar la opción predeterminada
        foreach (array_merge_recursive($configs['app'], $configs['vendors']) as $option => $types) {
            if ($input->hasOption($option) && $input->getOption($option)) {
                $noOptionApplied = false;
                break;
            }
        }

        if ($noOptionApplied && !$input->getOption('all')) {
            $input->setOption($configs['default_option'], true);
        }

        // Si no se configuraron vendors o en caso contrario si no se solicita solamente buscar en los vendors
        if (!$vendorsOptionsConfigured || !$input->getOption('only-vendors')) {
            foreach ($configs['app'] as $option => $types) {
                if ($input->getOption($option) || $input->getOption('all')) {
                    foreach ($types as $type => $directories) {
                        $this->options[$option][] = $type;

                        $this->directories[$type] = $directories;
                    }
                }
            }
        }

        if ($vendorsOptionsConfigured) {
            // Si se configuraron los vendors y se solicita solo buscar en los vendors
            if ($input->getOption('only-vendors')) {
                foreach ($configs['vendors'] as $option => $types) {
                    if ($input->getOption($option) || $input->getOption('all')) {
                        foreach ($types as $type => $directories) {
                            $this->options[$option][] = $type;

                            $this->directories[$type] = $directories;
                        }
                    }
                }
            }

            // Si se solicita buscar "también" en los vendors
            if ($input->getOption('include-vendors')) {
                foreach ($configs['vendors'] as $option => $types) {
                    if (($input->hasOption($option) && $input->getOption($option)) || $input->getOption('all')) {
                        foreach ($types as $type => $directories) {
                            $this->options[$option][] = $type;

                            if (!isset($this->directories[$type])) {
                                $this->directories[$type] = $directories;
                            } else {
                                $this->directories[$type] = array_merge($this->directories[$type], $directories);
                            }

                            $this->options[$option] = array_unique($this->options[$option]);
                            $this->directories[$type] = array_unique($this->directories[$type]);
                        }
                    }
                }
            }
        }
    }

    private function createFinder(InputInterface $input, SymfonyStyle $io, $patterns): Finder
    {
        $typesToSearch = [];
        $directoriesToSearch = [];
        $msgDirectorieList = [];

        foreach ($this->directories as $type => $directories) {
            $typesToSearch[] = $type;
            $directoriesToSearch = array_merge($directoriesToSearch, $directories);

            $msgDirectorieList[] = sprintf("$type: <comment>%s</comment>", '"'.implode('", "', $directories).'"');
        }

        // Elimina duplicaciones
        $typesToSearch = array_unique($typesToSearch);
        $directoriesToSearch = array_unique($directoriesToSearch);

        // Ordena los arrays
        sort($typesToSearch);
        sort($directoriesToSearch);

        $io->section(sprintf(
            'Iniciando la busqueda para archivos: <comment>%s</comment>',
            '"'.implode('", "', $typesToSearch).'"')
        );

        // Muestra los patrones a ser buscados
        $auxToPrint = '"'.implode('", "', $patterns).'"';

        if ($input->getOption('i')) {
            $msgCase = 'icase sensitive';
        } else {
            $msgCase = 'case sensitive';
        }

        $io->text("Patrones a buscar: <comment>$auxToPrint</comment> [<info>$msgCase</info>]");
        $io->newLine();

        $io->text($this->translator->trans('search_in_code.msgs.searching_in_this_directories'));
        $io->listing($msgDirectorieList);

        $finder = new Finder();

        // Asigna los directorios en donde buscar
        foreach ($directoriesToSearch as $directory) {
            $finder->in($directory);
        }

        // Asigna las extensiones de archivos de acuerdo a las opciones
        foreach ($typesToSearch as $type) {
            $finder->name('*.'.$type);
        }

        // Asigna los patrones de texto a buscar
        foreach ($patterns as $pattern) {
            if ($input->getOption('i')) {
                $finder->contains('/'.$pattern.'/i');
            } else {
                $finder->contains('/'.$pattern.'/');
            }
        }

        // Ordenar por extensión, path y nombre asc
        $finder->sort(
            function (\SplFileInfo $a, \SplFileInfo $b) {
                return strcmp($a->getExtension().'.'.$a->getRealPath(), $b->getExtension().'.'.$b->getRealPath());
            }
        );

        return $finder;
    }

    private function showResults(InputInterface $input, OutputInterface $output, SymfonyStyle $io, Finder $finder, array $patterns)
    {
        $escapedPatterns = '/('.implode('|', $patterns).')/';

        if ($input->getOption('i')) {
            $escapedPatterns .= 'i';
        }

        $progressSection = $output->section();
        $progress = new ProgressBar($progressSection, $finder->count());

        $io->newLine();

        $table = new Table($output);

        $headers = [
            $this->translator->trans('search_in_code.table_titles.number'),
            $this->translator->trans('search_in_code.table_titles.filename'),
            $this->translator->trans('search_in_code.table_titles.file_number'),
            $this->translator->trans('search_in_code.table_titles.pattern'),
        ];

        $table
            ->setStyle('box-double')
            ->setHeaders($headers);

        $rows = [];

        $number = 0;
        $filesCount = 0;

        $type = null;
        $fileName = null;

        /**
         * Se recorre cada archivo encontrado.
         *
         * @var SplFileInfo $file
         */
        foreach ($finder->files() as $file) {
            //sleep(1);
            ++$filesCount;

            $splFile = new \SplFileObject($file);
            $grepped = new \RegexIterator($splFile, $escapedPatterns);

            // Se recorre cada coincidencia de los patrones encontrados dentro de este archivo
            foreach ($grepped as $i => $text) {
                ++$number;

                $row = $this->prettifyRow(
                    $input,
                    $number,
                    $file->getPathname(),
                    $file->getExtension(),
                    ($i + 1),
                    $text,
                    $escapedPatterns
                );

                if (!$input->getOption('csv')) {
                    if ($fileName === $file->getPathname()) {
                        $row['file_name'] = '';
                    }

                    $fileName = $file->getPathname();

                    if ($type !== $row['file_type']) {
                        $type = $row['file_type'];

                        $option = $this->getOptionFromFileType($type);

                        $tableCell = new TableCell('<comment>'.$option.'</comment>', ['colspan' => 4]);

                        if ($number > 1) {
                            $rows[] = new TableSeparator();
                        }

                        $rows[] = [$tableCell];

                        if (1 == $number) {
                            $rows[] = new TableSeparator();
                        }
                    }

                    if ($number > 1) {
                        $rows[] = new TableSeparator();
                    }
                }

                unset($row['file_type']);

                $rows[] = $row;
            }

            $progress->setProgress($filesCount);
        }

        $progress->finish();
        $progressSection->clear();

        $table->addRows($rows);

        if (!empty($rows)) {
            if ($input->getOption('csv')) {
                array_unshift($rows, $headers);

                foreach ($rows as $row) {
                    $io->text('"'.implode('", "', $row).'"');
                }
            } else {
                $table->render();
            }
        }

        $io->newLine();
        $io->text($this->translator->trans('search_in_code.msgs.files_found', ['%count%' => $finder->count()]));
        $io->text($this->translator->trans('search_in_code.msgs.lines_found', ['%count%' => $number]));
    }

    private function prettifyRow(InputInterface $input, $number, $fileName, $fileType, $fileLineNumber, $fileLineText, $escapedPatterns)
    {
        // se eliminan espacios antes y despues
        $fileLineText = trim($fileLineText);
        // se eliminan excesos de espacios dentro
        $fileLineText = preg_replace('/\s+/', ' ', $fileLineText);
        // se agrega color a los patrones encontrados
        $fileLineText = preg_replace($escapedPatterns, '<error>$1</error>', $fileLineText);
        // en caso de que antes de la etiqueta <error> haya una barra invertida agrego un espacio para que no
        // intente escapar el caracter "<"
        $fileLineText = str_replace('\\<error>', '\\ <error>', $fileLineText);

        $wordWrapSize = $input->getOption('word-wrap');

        // si el nombre del archivo es muy largo lo corto
        if (strlen($fileName) > $wordWrapSize) {
            $fileName = wordwrap($fileName, $wordWrapSize, PHP_EOL, true);
        }

        // si el texto de la linea es muy largo lo corto
        if (strlen($fileLineText) > $wordWrapSize) {
            $fileLineText = $this->wordwrapWithHtml($fileLineText, 'error', $wordWrapSize);
        }

        // se pinta la palabra vendor en la ruta del archivo
        $fileName = preg_replace("/^vendor\//", '<comment>$0</comment>', $fileName);

        $ret = [
            'number' => $number,
            'file_name' => $fileName,
            'file_line_number' => $fileLineNumber,
            'file_line_text' => $fileLineText,
            'file_type' => $fileType,
        ];

        return $ret;
    }

    private function wordwrapWithHtml(string $str, string $tag, int $maxlength = 60): string
    {
        $openTag = chr(169);
        $closeTag = chr(174);
        $separator = PHP_EOL;

        $str = str_replace("<$tag>", $openTag, $str);
        $str = str_replace("</$tag>", $closeTag, $str);

        $betweenTags = false;
        $howManyChars = 0;
        $ret = '';

        $chars = str_split($str);

        foreach ($chars as $char) {
            if ($char == $openTag) {
                $betweenTags = true;
            } elseif ($char == $closeTag) {
                $betweenTags = false;
            } else {
                ++$howManyChars;
            }

            if ($howManyChars < $maxlength) {
                $ret .= $char;
            } else {
                if ($betweenTags) {
                    $ret .= $char;
                } else {
                    $ret .= ($char.$separator);
                    $howManyChars = 0;
                }
            }
        }

        $ret = str_replace($openTag, "<$tag>", $ret);
        $ret = str_replace($closeTag, "</$tag>", $ret);

        return $ret;
    }

    private function getOptionFromFileType(string $type): ?string
    {
        foreach ($this->options as $option => $types) {
            if (false !== array_search($type, $types)) {
                return $option;
            }
        }

        return null;
    }

    private function getWordWrapDefault()
    {
        $terminalWidth = (new Terminal())->getWidth();

        $wordWrapSize = ($terminalWidth - 20) / 2;

        if ($terminalWidth <= 106) {
            $wordWrapSize = ($terminalWidth - 35) / 2;
        } elseif ($terminalWidth <= 130) {
            $wordWrapSize = ($terminalWidth - 30) / 2;
        }

        return $wordWrapSize;
    }
}
