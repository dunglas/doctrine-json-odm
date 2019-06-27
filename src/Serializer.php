<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Symfony\Component\Serializer\Serializer as BaseSerializer;

class Serializer extends BaseSerializer
{
    const KEY_TYPE = '#type';
    const KEY_SCALAR = '#scalar';

    public function normalize($data, $format = null, array $context = array())
    {
        $normalizedData = parent::normalize($data, $format, $context);

        if (is_object($data)) {
            $typeData = [self::KEY_TYPE => get_class($data)];
            $valueData = is_scalar($normalizedData) ? [self::KEY_SCALAR => $normalizedData] : $normalizedData;
            $normalizedData = array_merge($typeData, $valueData);
        }

        return $normalizedData;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (is_array($data) && (isset($data[self::KEY_TYPE]))) {
            $type = $data[self::KEY_TYPE];
            unset($data[self::KEY_TYPE]);

            $data = isset($data[self::KEY_SCALAR]) ? $data[self::KEY_SCALAR] : $data;
            $data = $this->denormalize($data, $type, $format, $context);
            return parent::denormalize($data, $type, $format, $context);
        }

        if (is_array($data) || $data instanceof \Traversable) {
            $class = ($class === '') ? 'stdClass' : $class;
            return parent::denormalize($data, $class.'[]', $format, $context);
        }

        return $data;
    }
}
