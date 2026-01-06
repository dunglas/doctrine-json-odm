<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Type;

use Doctrine\DBAL\Types\JsonbType;

/**
 * The JSONB document type.
 *
 * Requires Doctrine DBAL 4.3.0+.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class JsonbDocumentType extends JsonbType
{
    use JsonDocumentTypeTrait;

    public const NAME = 'jsonb_document';

    public function getName(): string
    {
        return self::NAME;
    }
}
