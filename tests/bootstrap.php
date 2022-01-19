<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

date_default_timezone_set('UTC');

$loader = require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Fixtures/AppKernel.php';

return $loader;
