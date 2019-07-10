<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeServicesPublicPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        static $services = [
            'doctrine.orm.default_entity_manager',
            'doctrine.dbal.default_connection',
            'doctrine',
        ];

        foreach ($services as $service) {
            if (!$container->hasDefinition($service)) {
                continue;
            }

            $definition = $container->getDefinition($service);
            $definition->setPublic(true);
        }
    }
}
