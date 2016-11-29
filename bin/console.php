#!/usr/bin/env php
<?php
require __DIR__.'/../vendor/autoload.php';

use Commercetools\Core\Command\GenerateClassesCommand;
use Commercetools\Core\Command\WatchCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new GenerateClassesCommand());
$application->add(new WatchCommand());

$application->run();

