<?php

namespace Micayael\CommandsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class SearchInFilesCommand extends Command
{
    private $options = [];
    private $directories = [];
    private $vendorDirectories = [];

    public function __construct(array $bundleConfig)
    {
        parent::__construct();

        foreach ($bundleConfig['options']['project'] as $option => $extensions) {
            foreach ($extensions as $extension => $folders) {
                $this->options[$option][] = $extension;
                $this->options['all'][] = $extension;
                $this->directories[$extension] = $folders;
            }
        }

        // Compila las extensiones de archivos de estilos y scripts
        $this->options['assets'] = array_merge($this->options['styles'], $this->options['scripts']);

        // Mueve la posición "all" al final
        $aux = $this->options['all'];
        unset($this->options['all']);
        $this->options['all'] = $aux;

        if (isset($bundleConfig['options']['vendors'])) {
            foreach ($bundleConfig['options']['vendors'] as $option => $extensions) {
                foreach ($extensions as $extension => $folders) {
                    $this->vendorDirectories[$extension] = $folders;
                }
            }
        }
    }

    protected function configure()
    {
        $this->setName('app:search')
            ->setDescription('Busca dentro del proyecto errores en el código')
            ->addArgument(
                'patterns',
                InputArgument::IS_ARRAY,
                'Patrones a ser buscados en el código. Pueden ser varios y contener expresiones regulares.'
            )
            ->addOption(
                'php',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en los archivos que contienen php'
            )
            ->addOption(
                'views',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en los archivos twig'
            )
            ->addOption(
                'config',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en los archivos yml'
            )
            ->addOption(
                'styles',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en los archivos css y sass'
            )
            ->addOption(
                'scripts',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en los archivos js'
            )
            ->addOption(
                'assets',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en los archivos css y js'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Si se usa busca los patrones en todos los tipos de archivos'
            )
            ->addOption(
                'include-vendors',
                null,
                InputOption::VALUE_NONE,
                'Si se usa incluye carpetas vendors como el core del frontend'
            )
            ->addOption(
                'i',
                '-i',
                InputOption::VALUE_NONE,
                'Indica si se quiere realizar la busqueda sin importar mayúsculas o minúsculas. Por defecto es case sensitive.'
            )
            ->setHelp(
                <<<EOF
El comando <info>%command.name%</info> busca dentro del proyecto ciertos patrones de texto que pueden
se buscados según se indiquen las opciones. Pueden ser ingresados varios patrones separados por un espacio. En caso
de que se desee enviar caracteres especiales en los patrones, estos deberán ser encerrados con comillas simples. También
es posible enviar entre comillas expresiones regulares para ser buscadas.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Buscando patrones de texto');

        $patterns = $input->getArgument('patterns');

        if (empty($patterns)) {
            $patterns = $io->ask('Qué texto o textos desea buscar?');
            $patterns = explode(' ', $patterns);
        }

        // Si se ingresa la opción --include-vendors, hago merge de los directorios
        if ($input->getOption('include-vendors')) {
            foreach ($this->directories as $type => $directories) {
                if (isset($this->vendorDirectories[$type])) {
                    $this->directories[$type] = array_merge($this->directories[$type], $this->vendorDirectories[$type]);
                }
            }
        }

        $finder = $this->getFinder($input, $output, $io, $patterns);

        $this->search($input, $output, $io, $finder, $patterns);

        return 0;
    }

    private function getFinder(InputInterface $input, OutputInterface $output, SymfonyStyle $io, $patterns)
    {
        $finder = new Finder();

        $typesToSearch = array();
        $directoriesToSearch = array();

        // Evaluá las opciones ingresadas
        foreach ($this->options as $option => $types) {
            if ($input->getOption($option)) {
                $typesToSearch = array_merge($typesToSearch, $types);

                foreach ($types as $type) {
                    $directoriesToSearch = array_merge($directoriesToSearch, $this->directories[$type]);
                }
            }
        }

        // Elimina duplicaciones y ordena los arrays para extensiones y directorios
        $typesToSearch = array_unique($typesToSearch);
        $directoriesToSearch = array_unique($directoriesToSearch);
        sort($typesToSearch);
        sort($directoriesToSearch);

        // Evalúa si no se ingresaron opciones para usar una por defecto
        if (empty($typesToSearch)) {
            $typesToSearch = array('php');
            $directoriesToSearch = $this->directories['php'];

            $io->section('Se busca por defecto en archivos <comment>"php"</comment>');
        } else {
            $auxToPrint = '"'.implode('", "', $typesToSearch).'"';

            $io->section("Iniciando la busqueda para archivos: <comment>$auxToPrint</comment>");
        }

        // Muestra los patrones a ser buscados
        $auxToPrint = '"'.implode('", "', $patterns).'"';

        if ($input->getOption('i')) {
            $msgCase = 'icase sensitive';
        } else {
            $msgCase = 'case sensitive';
        }

        $io->text("Patrones a buscar: <comment>$auxToPrint</comment> [<info>$msgCase</info>]");

        // Si es verbose muestra los directorios en donde se realizarán las búsquedas
        if ($output->isVerbose()) {
            $io->text('Se busca en los siguientes directorios:');

            $msgList = [];

            foreach ($this->directories as $type => $dirs) {
                if (in_array($type, $typesToSearch)) {
                    $auxToPrint = '"'.implode('", "', $dirs).'"';

                    $msgList[] = "$type: <comment>$auxToPrint</comment>";
                }
            }

            $io->listing($msgList);
        }

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

        // Opciones adicionales
        $finder
            ->files();

        return $finder;
    }

    private function search(InputInterface $input, OutputInterface $output, SymfonyStyle $io, Finder $finder, array $patterns)
    {
        $escapedPatterns = '/('.implode('|', $patterns).')/';

        if ($input->getOption('i')) {
            $escapedPatterns .= 'i';
        }

        $nro = 1;
        $rows = array();
        // para prever la duplicacion de archivos en el finder.
        $files = array();

        // Por cada archivo encontrado busco en su interior
        foreach ($finder as $file) {
            $filename = $file->getPath().'/'.$file->getFilename();

            if (in_array($filename, $files)) {
                break;
            }

            $files[] = $filename;

            $splFile = new \SplFileObject($file);
            $grepped = new \RegexIterator($splFile, $escapedPatterns);

            foreach ($grepped as $i => $text) {
                $rows[] = $this->prettifyResults(
                    $nro++,
                    $filename,
                    $file->getExtension(),
                    ($i + 1),
                    $text,
                    $escapedPatterns
                );
            }
        }

        if ($output->isVerbose()) {
            $io->text('Archivos encontrados:');

            $filesStyled = [];

            foreach ($files as $f) {
                $filesStyled[] = '<comment>'.$f.'</comment>';
            }

            $io->listing($filesStyled);
        }

        $io->text(sprintf('Resultados encontrados: <comment>%d</comment>', count($rows)));
        $io->newLine();

        if ($rows) {
            $this->showResults($output, $rows);
        }

        return $rows;
    }

    private function prettifyResults($number, $fileName, $extension, $fileLineNumber, $fileLineText, $escapedPatterns)
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

        // si el texto de la linea es muy largo lo corto
        if (strlen($fileLineText) > 60) {
            $fileLineText = $this->cutText($fileLineText, 'error');
        }

        // si el nombre del archivo es muy largo lo corto
        if (strlen($fileName) > 60) {
            $fileName = wordwrap($fileName, 60, PHP_EOL, true);
        }

        // se pinta la palabra vendor en la ruta del archivo
        $fileName = preg_replace("/^vendor\//", '<comment>$0</comment>', $fileName);

        $ret = array(
            $number,
            $fileName,
            $extension,
            $fileLineNumber,
            $fileLineText,
        );

        return $ret;
    }

    private function showResults(OutputInterface $output, array $rows)
    {
        $table = new Table($output);

        $type = null;

        foreach ($rows as $key => $row) {
            if ($type != $row[2]) {
                $type = $row[2];
                $option = $this->getOptionName($type);
                $table->addRow(array(new TableCell('<comment>'.$option.'</comment>', array('colspan' => 4))));
                $table->addRow(new TableSeparator());
            }

            unset($row[2]);

            $table->addRow($row);

            // si no es la última fila agrego un separador
            if ($key != count($rows) - 1) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->setHeaders(
            array('N°', 'File', 'Line', 'Pattern')
        );

        $table->render();
    }

    private function cutText($str, $tag, $maxlength = 60)
    {
        $openTag = chr(169);
        $closeTag = chr(174);
        $separator = "\n";

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

    private function getOptionName($extension)
    {
        foreach ($this->options as $name => $extensions) {
            if (array_search($extension, $extensions) !== false) {
                return $name;
            }
        }

        return;
    }
}
