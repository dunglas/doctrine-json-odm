<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
trait SerializerTrait
{
    /**
     * @var TypeMapperInterface|null
     */
    private $typeMapper;

    /**
     * @param (NormalizerInterface|DenormalizerInterface)[] $normalizers
     * @param (EncoderInterface|DecoderInterface)[]         $encoders
     */
    public function __construct(array $normalizers = [], array $encoders = [], ?TypeMapperInterface $typeMapper = null)
    {
        parent::__construct($normalizers, $encoders);

        $this->typeMapper = $typeMapper;
    }

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
            $typeName = \get_class($data);

            if ($this->typeMapper) {
                $typeName = $this->typeMapper->getTypeByClass($typeName);
            }

            $normalizedData = is_scalar($normalizedData) ? [self::KEY_SCALAR => $normalizedData] : $normalizedData;
            $normalizedData[self::KEY_TYPE] = $typeName;
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
            $keyType = $data[self::KEY_TYPE];

            if ($this->typeMapper) {
                $keyType = $this->typeMapper->getClassByType($keyType);
            }

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
