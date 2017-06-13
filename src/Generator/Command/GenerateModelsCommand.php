<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator\Command;

use Commercetools\Generator\ModelGenerator;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use Lurker\Tracker\InotifyTracker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModelsCommand extends Command
{
    /**
     * @var ModelGenerator
     */
    private $generator;
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('generate')
            // the short description shown while running "php bin/console list"
            ->setDescription('Generates data model classes')
            ->addOption('watch', 'w', InputOption::VALUE_NONE)
            ->addOption('restart', 'r', InputOption::VALUE_NONE)
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = __DIR__ . '/../../Model';
        $outputPath = __DIR__ . '/../../../generated/Model';
        $this->generator = new ModelGenerator(
            $path,
            $outputPath,
            'Commercetools\\Model'
        );
        $this->generator->run();
    }
}
