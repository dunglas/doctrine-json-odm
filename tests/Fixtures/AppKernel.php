<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
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

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('framework', [
            'secret' => 'jsonodm',
            'test' => null,
        ]);

        $db = getenv('DB');
        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'MYSQL' === $db ? 'pdo_mysql' : 'pdo_pgsql',
                'host' => getenv("{$db}_HOST"),
                'dbname' => getenv("{$db}_DBNAME"),
                'user' => getenv("{$db}_USER"),
                'password' => getenv("{$db}_PASSWORD"),
                'charset' => 'UTF8',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
            ],
        ]);

        // Make a few services public until we depend on Symfony 4.1+ and can use the new test container
        $container->addCompilerPass(new MakeServicesPublicPass());
    }
}
