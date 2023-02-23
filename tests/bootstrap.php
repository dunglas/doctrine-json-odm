<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

date_default_timezone_set('UTC');

$loader = require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Fixtures/AppKernel.php';

if (!isset($_SERVER['DATABASE_URL'])) {
    (new SymfonyStyle(new ArrayInput([]), new ConsoleOutput()))->error("DATABASE_URL environment var is not set.\nCheck the CONTRIBUTING.md file for more details about how to test this project.");

    exit(1);
}

return $loader;
