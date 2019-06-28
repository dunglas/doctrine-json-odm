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
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;

class InjectCustomNormalizerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setDefinition('dunglas_doctrine_json_odm.normalizer.custom', new Definition(CustomNormalizer::class));

        $serializerDefinition = $container->getDefinition('dunglas_doctrine_json_odm.serializer');
        $normalizers = array_merge([new Reference('dunglas_doctrine_json_odm.normalizer.custom')], $serializerDefinition->getArgument(0));
        $serializerDefinition->setArgument(0, $normalizers);
    }
}
