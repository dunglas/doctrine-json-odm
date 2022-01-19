<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Symfony\Component\Serializer\Serializer as BaseSerializer;

final class Serializer extends BaseSerializer
{
    private const KEY_TYPE = '#type';
    private const KEY_SCALAR = '#scalar';

    public function normalize($data, $format = null, array $context = [])
    {
        $normalizedData = parent::normalize($data, $format, $context);

        if (\is_object($data)) {
            $typeData = [self::KEY_TYPE => \get_class($data)];
            $valueData = is_scalar($normalizedData) ? [self::KEY_SCALAR => $normalizedData] : $normalizedData;
            $normalizedData = array_merge($typeData, $valueData);
        }

        return $normalizedData;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (\is_array($data) && (isset($data[self::KEY_TYPE]))) {
            $keyType = $data[self::KEY_TYPE];
            unset($data[self::KEY_TYPE]);

            $data = $data[self::KEY_SCALAR] ?? $data;
            $data = $this->denormalize($data, $keyType, $format, $context);

            return parent::denormalize($data, $keyType, $format, $context);
        }

        if (is_iterable($data)) {
            $type = ('' === $type) ? 'stdClass' : $type;

            return parent::denormalize($data, $type.'[]', $format, $context);
        }

        return $data;
    }
}
