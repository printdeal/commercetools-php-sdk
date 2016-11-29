<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Command;

use Commercetools\Core\Helper\Generate\ClassGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('watch')
            // the short description shown while running "php bin/console list"
            ->setDescription('Watches class generator files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo 'Start watching' . PHP_EOL;
        do {
            passthru(__DIR__ . '/../../bin/console.php generate -w -r');
            echo 'Restarting generator' . PHP_EOL;
        } while (true);
    }
}
