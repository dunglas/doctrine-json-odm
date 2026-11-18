<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Bundle\DependencyInjection;

use Doctrine\DBAL\Types\JsonbType;
use Dunglas\DoctrineJsonOdm\Type\JsonbDocumentType;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;

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
        $this->prependDoctrineTypes($container);

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
     * Registers the types through DoctrineBundle instead of the process-wide `Type` registry, which
     * DBAL 4.6 deprecates and DBAL 5 removes.
     */
    private function prependDoctrineTypes(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('doctrine')) {
            return;
        }

        $types = [JsonDocumentType::NAME => JsonDocumentType::class];

        if (class_exists(JsonbType::class)) {
            $types[JsonbDocumentType::NAME] = JsonbDocumentType::class;
        }

        // Prepended, so a type a user declares under the same name still wins.
        $container->prependExtensionConfig('doctrine', ['dbal' => ['types' => $types]]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        if (PHP_VERSION_ID < 80100 || !class_exists(BackedEnumNormalizer::class)) {
            $container->removeDefinition('dunglas_doctrine_json_odm.normalizer.backed_enum');
        }

        if (!class_exists(UidNormalizer::class)) {
            $container->removeDefinition('dunglas_doctrine_json_odm.normalizer.uid');
        }

        $config = $this->processConfiguration(new Configuration(), $configs);

        if ($config['type_map'] ?? []) {
            $container->getDefinition('dunglas_doctrine_json_odm.type_mapper')->addArgument($config['type_map']);
        } else {
            $container->removeDefinition('dunglas_doctrine_json_odm.type_mapper');
        }
    }
}
