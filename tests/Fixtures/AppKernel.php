<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Dunglas\DoctrineJsonOdm\Bundle\DunglasDoctrineJsonOdmBundle;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\TestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test purpose micro-kernel.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DunglasDoctrineJsonOdmBundle(),
            new TestBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'jsonodm',
            'test' => null,
        ]);

        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_pgsql',
                'host' => '127.0.0.1',
                'dbname' => 'jsonodm',
                'user' => 'dunglas',
                'password' => '',
                'charset' => 'UTF8',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
            ],
        ]);
    }
}
