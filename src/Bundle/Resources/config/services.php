<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Dunglas\DoctrineJsonOdm\Serializer;
use Dunglas\DoctrineJsonOdm\TypeMapper;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('dunglas_doctrine_json_odm.normalizer.backed_enum', BackedEnumNormalizer::class)
        ->private();

    $services->set('dunglas_doctrine_json_odm.normalizer.uid', UidNormalizer::class)
        ->private();

    $services->set('dunglas_doctrine_json_odm.normalizer.datetime', DateTimeNormalizer::class)
        ->private();

    $services->set('dunglas_doctrine_json_odm.normalizer.array', ArrayDenormalizer::class)
        ->private();

    $services->set('dunglas_doctrine_json_odm.type_mapper', TypeMapper::class)
        ->private();

    $services->set('dunglas_doctrine_json_odm.normalizer.object', ObjectNormalizer::class)
        ->private()
        ->args([
            service('serializer.mapping.class_metadata_factory')->ignoreOnInvalid(),
            null,
            service('serializer.property_accessor'),
            service('property_info')->ignoreOnInvalid(),
            service('serializer.mapping.class_discriminator_resolver')->ignoreOnInvalid(),
        ]);

    $services->set('dunglas_doctrine_json_odm.serializer', Serializer::class)
        ->public()
        ->args([
            [
                service('dunglas_doctrine_json_odm.normalizer.backed_enum')->ignoreOnInvalid(),
                service('dunglas_doctrine_json_odm.normalizer.uid')->ignoreOnInvalid(),
                service('dunglas_doctrine_json_odm.normalizer.datetime'),
                service('dunglas_doctrine_json_odm.normalizer.array'),
                service('dunglas_doctrine_json_odm.normalizer.object'),
            ],
            [
                service('serializer.encoder.json'),
            ],
            service('dunglas_doctrine_json_odm.type_mapper')->ignoreOnInvalid(),
        ]);
};
