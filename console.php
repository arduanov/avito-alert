#!/usr/bin/env php
<?php
$app = require_once(__DIR__ . '/src/bootstrap.php');
set_time_limit(0);

use Symfony\Component\Console\Application as Console;

$console = new Console();

$console->addCommands([
    new App\Command\WorkerCommand($app),
]);

$console->run();