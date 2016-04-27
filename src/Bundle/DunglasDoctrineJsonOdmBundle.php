<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Bundle;

use Doctrine\DBAL\Types\Type;
use Dunglas\DoctrineJsonOdm\JsonDocumentType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Doctrine JSON ODM integration with the Symfony framework.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class DunglasDoctrineJsonOdmBundle extends Bundle
{
    public function __construct()
    {
        Type::addType('json_document', JsonDocumentType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $type = Type::getType('json_document');
        $type->setNormalizer($this->container->get('serializer'));
        $type->setPropertyTypeExtractor($this->container->get('property_info'));
    }
}
