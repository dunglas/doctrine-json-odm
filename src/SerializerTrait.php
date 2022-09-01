<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

/**
 * @internal
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
trait SerializerTrait
{
    /**
     * @param mixed       $data
     * @param string|null $format
     *
     * @return array|\ArrayObject|bool|float|int|string|null
     */
    public function normalize($data, $format = null, array $context = [])
    {
        $normalizedData = parent::normalize($data, $format, $context);

        if (\is_object($data)) {
            $typeName = TypeMapper::getTypeByClass(\get_class($data));
            $typeData = [self::KEY_TYPE => $typeName];
            $valueData = is_scalar($normalizedData) ? [self::KEY_SCALAR => $normalizedData] : $normalizedData;
            $normalizedData = array_merge($typeData, $valueData);
        }

        return $normalizedData;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (\is_array($data) && (isset($data[self::KEY_TYPE]))) {
            $keyType = TypeMapper::getClassByType($data[self::KEY_TYPE]);
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
