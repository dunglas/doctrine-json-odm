<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures;

use AppKernel;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\CustomTypeMapper;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AppKernelWithCustomTypeMapper extends AppKernel
{
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        parent::configureContainer($container, $loader);

        $container->setDefinition('dunglas_doctrine_json_odm.type_mapper', new Definition(CustomTypeMapper::class));
    }
}
