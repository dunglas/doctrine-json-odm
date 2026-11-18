<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Bundle;

use Doctrine\DBAL\Types\JsonbType;
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
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $serializer = $this->container->get('dunglas_doctrine_json_odm.serializer');

        JsonDocumentType::setSharedSerializer($serializer);

        if (class_exists(JsonbType::class)) {
            JsonbDocumentType::setSharedSerializer($serializer);
        }
    }
}
