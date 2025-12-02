<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Bundle;

use Doctrine\DBAL\Types\Type;
use Dunglas\DoctrineJsonOdm\Type\JsonbDocumentType;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
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
        if (!Type::hasType('json_document')) {
            Type::addType('json_document', JsonDocumentType::class);
        }

        if (class_exists(\Doctrine\DBAL\Types\JsonbType::class) && !Type::hasType('jsonb_document')) {
            Type::addType('jsonb_document', JsonbDocumentType::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $serializer = $this->container->get('dunglas_doctrine_json_odm.serializer');

        $type = Type::getType('json_document');
        $type->setSerializer($serializer);

        if (Type::hasType('jsonb_document')) {
            $type = Type::getType('jsonb_document');
            $type->setSerializer($serializer);
        }
    }
}
