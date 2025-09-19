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
     * @return array|\ArrayObject|scalar|null
     */
    public function normalize($data, $format = null, array $context = [])
    {
        if (\is_array($data)) {
            $normData = [];
            foreach ($data as $idx => $datum) {
                $normData[$idx] = $this->normalize($datum, $format, $context);
            }

            return $normData;
        }

        if (\is_object($data)) {
            $typeName = \get_class($data);

            $normData = parent::normalize($data, $format, $context + [self::CONTEXT_SERIALIZER => $this]);

            if ($this->typeMapper) {
                $typeName = $this->typeMapper->getTypeByClass($typeName);
            }

            $typeData = [self::KEY_TYPE => $typeName];

            if (\is_array($normData) && !isset($normData[self::KEY_TYPE])) {
                $normData = $this->normalize($normData, $format, $context);
            }
            if (\is_scalar($normData)) {
                $normData = [self::KEY_SCALAR => $normData];
            }

            return \array_merge($typeData, $normData);
        }

        return $data;
    }

    /**
     * @param null|scalar|array $data
     * @param string $type
     * @param string|null $format
     *
     * @return mixed
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if (!\is_array($data)) {
            return $data;
        }

        if (isset($data[self::KEY_TYPE])) {
            $keyType = $data[self::KEY_TYPE];
            unset($data[self::KEY_TYPE]);

            if ($this->typeMapper) {
                $keyType = $this->typeMapper->getClassByType($keyType);
            }

            $data = $data[self::KEY_SCALAR] ?? $data;

            if (\is_array($data)) {
                foreach ($data as $idx => $datum) {
                    $data[$idx] = $this->denormalize($datum, $keyType, $format, $context);
                }
            }

            return parent::denormalize($data, $keyType, $format, $context + [self::CONTEXT_SERIALIZER => $this]);
        }

        foreach ($data as $idx => $datum) {
            $data[$idx] = $this->denormalize($datum, '', $format, $context);
        }

        return $data;
    }
}
