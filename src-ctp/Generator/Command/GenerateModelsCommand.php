<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Ctp\Generator\Command;

use Ctp\Generator\AnnotationRunner;
use Ctp\Generator\ClassMapProcessor;
use Ctp\Generator\CollectableProcessor;
use Ctp\Generator\CollectionTypeProcessor;
use Ctp\Generator\DeletableByIdRequestProcessor;
use Ctp\Generator\DeletableByKeyRequestProcessor;
use Ctp\Generator\DiscriminatorProcessor;
use Ctp\Generator\QueryableByIdRequestProcessor;
use Ctp\Generator\QueryableByKeyRequestProcessor;
use Ctp\Generator\QueryableRequestProcessor;
use Ctp\Generator\QueryableResultProcessor;
use Ctp\Generator\ReferenceableProcessor;
use Ctp\Generator\ResourceProcessor;
use Ctp\Generator\UpdatableByIdRequestProcessor;
use Ctp\Generator\UpdatableByKeyRequestProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModelsCommand extends Command
{
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
        $namespace = 'Ctp\\Model';
        $path = realpath(__DIR__ . '/../../Model');
        $outputDir = realpath(__DIR__ . '/../../../target');

        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($outputDir));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $file) {
            unlink($file);
        }
        $this->ensureDirExists($outputDir);
        $outputPathClient = realpath($outputDir) . '/Client';
        $outputPathModel = realpath($outputDir) . '/Model';

        $processors = [
            new ReferenceableProcessor($path, $outputPathModel),
            new CollectableProcessor($path, $outputPathModel),
            new QueryableResultProcessor($path, $outputPathModel),
        ];
        $runner = new AnnotationRunner($path, $processors);
        $runner->run();

        $processors = [
            new ResourceProcessor($path, $outputPathModel),
            new CollectionTypeProcessor($path, $outputPathModel),
            new QueryableRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new QueryableByIdRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new QueryableByKeyRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new DeletableByIdRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new DeletableByKeyRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new UpdatableByIdRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new UpdatableByKeyRequestProcessor('Ctp\Client', $path, $outputPathClient),
            new DiscriminatorProcessor($path, $outputPathModel),
            new ClassMapProcessor($namespace, $outputPathModel),
        ];
        $runner = new AnnotationRunner([$path, $outputPathModel], $processors);
        $runner->run();
    }

    protected function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
