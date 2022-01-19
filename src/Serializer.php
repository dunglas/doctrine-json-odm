<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer as BaseSerializer;

if (method_exists(ArrayDenormalizer::class, 'setSerializer')) {
    // Symfony <=5.4
    final class Serializer extends BaseSerializer
    {
        private const KEY_TYPE = '#type';
        private const KEY_SCALAR = '#scalar';

        use SerializerTrait;
    }
} else {
    // Symfony >=6.0
    final class Serializer extends BaseSerializer
    {
        private const KEY_TYPE = '#type';
        private const KEY_SCALAR = '#scalar';

        use TypedSerializerTrait;
    }
}
