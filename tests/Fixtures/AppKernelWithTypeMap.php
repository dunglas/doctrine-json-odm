<?php

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures;

use AppKernel;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\WithMappedType;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppKernelWithTypeMap extends AppKernel
{
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        parent::configureContainer($container, $loader);

        $container->loadFromExtension('dunglas_doctrine_json_odm', [
            'types' => [
                'mappedType' => WithMappedType::class
            ]
        ]);
    }

}
