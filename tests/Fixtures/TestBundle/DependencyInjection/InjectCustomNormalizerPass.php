<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\DependencyInjection;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\Normalizer\VectorNormalizer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

class InjectCustomNormalizerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->setDefinition('dunglas_doctrine_json_odm.normalizer.custom', new Definition(CustomNormalizer::class));

        $vectorDefinition = new Definition(VectorNormalizer::class);
        $vectorDefinition->addTag('serializer.normalizer');
        $container->setDefinition(VectorNormalizer::class, $vectorDefinition);

        $serializerDefinition = $container->getDefinition('dunglas_doctrine_json_odm.serializer');
        $arguments = $serializerDefinition->getArguments();
        $arguments[0] = array_merge([
            new Reference(VectorNormalizer::class),
            new Reference('dunglas_doctrine_json_odm.normalizer.custom'),
        ], $arguments[0]);
        $serializerDefinition->setArguments($arguments);
    }
}
