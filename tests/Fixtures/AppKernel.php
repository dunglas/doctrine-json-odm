<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\DBAL\Types\JsonbType;
use Doctrine\ORM\Proxy\Proxy;
use Dunglas\DoctrineJsonOdm\Bundle\DunglasDoctrineJsonOdmBundle;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\DependencyInjection\MakeServicesPublicPass;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\TestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Test purpose micro-kernel.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DunglasDoctrineJsonOdmBundle(),
            new TestBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'secret' => 'jsonodm',
            'test' => null,
            'http_method_override' => false,
        ]);

        $orm = ['auto_mapping' => true];
//        $ormMappings = [
//            'TestBundle' => [
//                'is_bundle' => false,
//                'type' => 'attribute',
//                'dir' => __DIR__.'/TestBundle/Entity',
//                'prefix' => 'Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity',
//                'alias' => 'TestBundle',
//            ],
//        ];
//
//        if (class_exists(JsonbType::class)) {
//            $ormMappings['TestBundleJsonb'] = [
//                'is_bundle' => false,
//                'type' => 'attribute',
//                'dir' => __DIR__.'/TestBundle/Entity/Jsonb',
//                'prefix' => 'Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Jsonb',
//                'alias' => 'TestBundleJsonb',
//            ];
//        }
//        $orm = ['controller_resolver' => ['auto_mapping' => false], 'mappings' => $ormMappings];

        if (\PHP_VERSION_ID >= 80400 && !interface_exists(Proxy::class)) {
            $orm['enable_native_lazy_objects'] = true;
        } else {
            $orm['auto_generate_proxy_classes'] = true;
        }

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'url' => '%env(resolve:DATABASE_URL)%',
            ],
            'orm' => $orm,
        ]);

        // Make a few services public until we depend on Symfony 4.1+ and can use the new test container
        $container->addCompilerPass(new MakeServicesPublicPass());
    }
}
