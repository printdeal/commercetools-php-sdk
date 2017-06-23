<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator\Command;

use Commercetools\Generator\AnnotationRunner;
use Commercetools\Generator\ClassMapProcessor;
use Commercetools\Generator\CollectableProcessor;
use Commercetools\Generator\CollectionType;
use Commercetools\Generator\CollectionTypeProcessor;
use Commercetools\Generator\DiscriminatorProcessor;
use Commercetools\Generator\DiscriminatorValue;
use Commercetools\Generator\DiscriminatorValueProcessor;
use Commercetools\Generator\ModelGenerator;
use Commercetools\Generator\Queryable;
use Commercetools\Generator\QueryableRequestProcessor;
use Commercetools\Generator\QueryableResultProcessor;
use Commercetools\Generator\ReferenceableProcessor;
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
        $outputDir = __DIR__ . '/../../../target/Model';
        array_map('unlink', glob($outputDir . '/*.php'));
        array_map('unlink', glob($outputDir . '/**/*.php'));
        $this->ensureDirExists($outputDir);
        $outputPath = realpath($outputDir);

        $processors = [
            new ReferenceableProcessor($path, $outputPath),
            new CollectableProcessor($path, $outputPath),
            new QueryableResultProcessor($path, $outputPath),
        ];
        $runner = new AnnotationRunner($path, $processors);
        $runner->run();

        $processors = [
            new ResourceProcessor($path, $outputPath),
            new CollectionTypeProcessor($path, $outputPath),
            new QueryableRequestProcessor('Commercetools\Client', $path, dirname($outputPath) . '/Client'),
            new DiscriminatorProcessor($path, $outputPath),
            new ClassMapProcessor($namespace, $outputPath),
        ];
        $runner = new AnnotationRunner([$path, $outputPath], $processors);
        $runner->run();
    }

    protected function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
