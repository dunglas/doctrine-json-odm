<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;

date_default_timezone_set('UTC');

$loader = require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Fixtures/AppKernel.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

return $loader;
