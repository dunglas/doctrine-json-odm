<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Bundle\DependencyInjection;

use Dunglas\DoctrineJsonOdm\TypeMapper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class DunglasDoctrineJsonOdmExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        if (empty($frameworkConfiguration = $container->getExtensionConfig('framework'))) {
            return;
        }

        if (!isset($frameworkConfiguration['serializer']['enabled'])) {
            $container->prependExtensionConfig('framework', ['serializer' => ['enabled' => true]]);
        }

        if (!isset($frameworkConfiguration['property_info']['enabled'])) {
            $container->prependExtensionConfig('framework', ['property_info' => ['enabled' => true]]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (PHP_VERSION_ID < 80100 || !class_exists(BackedEnumNormalizer::class)) {
            $container->removeDefinition('dunglas_doctrine_json_odm.normalizer.backed_enum');
        }

        $config = $this->processConfiguration(new Configuration(), $configs);

        $typeMapConfig = $config['type_map'] ?? [];
        $container->setParameter('dunglas_doctrine_json_odm.type_map', $typeMapConfig);

        if (!$typeMapConfig) {
            $container->removeDefinition('dunglas_doctrine_json_odm.type_mapper');
        }
    }
}
