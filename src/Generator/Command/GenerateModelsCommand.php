<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator\Command;

use Commercetools\Generator\AnnotationRunner;
use Commercetools\Generator\ClassMapProcessor;
use Commercetools\Generator\DiscriminatorProcessor;
use Commercetools\Generator\ModelGenerator;
use Commercetools\Generator\ResourceProcessor;
use Symfony\Component\Console\Command\Command;
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
        $namespace = 'Commercetools\\Model';
        $path = realpath(__DIR__ . '/../../Model');
        $outputPath = realpath(__DIR__ . '/../../../generated/Model');

        $processors = [
            new ClassMapProcessor($namespace, $outputPath),
            new ResourceProcessor($namespace, $path, $outputPath),
            new DiscriminatorProcessor($path, $outputPath),
        ];
        $runner = new AnnotationRunner($path, $processors);

        $runner->run();
    }
}
