<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Command;

use Commercetools\Core\Helper\Generate\ClassGenerator;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use Lurker\Tracker\InotifyTracker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateClassesCommand extends Command
{
    /**
     * @var ClassGenerator
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
        $path = __DIR__ . '/../../templates';
        $outputPath = __DIR__ . '/../Data';
        $this->generator = new ClassGenerator(
            $path,
            $outputPath,
            'Commercetools\Core\Templates',
            'Commercetools\Core\Data'
        );
        $this->generator->run();

        if ($input->getOption('watch')) {
            $watcher = new ResourceWatcher();
            $watcher->track('code.templates', $path);
            $watcher->track('code.generator', __DIR__ . '/../Helper/Generate');

            $watcher->addListener('code.templates', function (FilesystemEvent $event) use ($input, $watcher) {
                echo date('c') . ': ' . $event->getResource() . ' was ' . $event->getTypeString() . PHP_EOL;

                if ($input->getOption('restart')) {
                    $watcher->stop();
                } else {
                    $this->generator->run();
                    echo $event->getResource() . ' was ' . $event->getTypeString() . PHP_EOL;
                }
            });
            $watcher->addListener('code.generator', function () use ($watcher) {
                $watcher->stop();
            });
            $watcher->start();
        }
    }
}
