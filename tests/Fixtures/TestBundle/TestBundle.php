<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\DependencyInjection\InjectCustomNormalizerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class TestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new InjectCustomNormalizerPass());
    }
}
