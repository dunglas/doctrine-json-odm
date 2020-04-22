<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Databrydge\Synchronizer\Domain\Connection\PackageType;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer as BaseSerializer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;

final class Serializer extends BaseSerializer
{
    private const KEY_TYPE = '#type';
    private const KEY_SCALAR = '#scalar';


    public function normalize($data, $format = null, array $context = [])
    {
        $normalizedData = parent::normalize($data, $format, $context);

        if (is_object($data)) {
            $typeData = [self::KEY_TYPE => get_class($data)];
            $valueData = is_scalar($normalizedData) ? [self::KEY_SCALAR => $normalizedData] : $normalizedData;
            $normalizedData = array_merge($typeData, $valueData);
        }

        return $normalizedData;
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (is_array($data) && (isset($data[self::KEY_TYPE]))) {
            $type = $data[self::KEY_TYPE];
            unset($data[self::KEY_TYPE]);

            $data = $data[self::KEY_SCALAR] ?? $data;

            return parent::denormalize($data, $type, $format, $context);
        }

        if (is_iterable($data)) {
            $class = ($class === '') ? 'stdClass' : $class;
            $arrayOfDocuments = true;
            foreach ($data as $row) {
                if (!is_array($row)) {
                    $arrayOfDocuments = false;
                    break;
                }

                if (!isset($row[self::KEY_TYPE])) {
                    $arrayOfDocuments = false;
                    break;
                }
            }

            if ($arrayOfDocuments) {
                return parent::denormalize($data, $class.'[]', $format, $context);
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}
