<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Transforms an object to an array with the following keys:
 * * _type: the class name
 * * _value: a representation of the values of the object.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class ObjectNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    private $objectNormalizer;

    public function __construct(NormalizerInterface $objectNormalizer)
    {
        if (!$objectNormalizer instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The normalizer used must implement the "%s" interface.', DenormalizerInterface::class));
        }

        $this->objectNormalizer = $objectNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            '_type' => get_class($object),
            '_value' => $this->objectNormalizer->normalize($object, $format, $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (isset($data['_value']) && isset($data['_type']) && is_array($data['_value'])) {
            $data['_value'] = $this->denormalize($data['_value'], $data['_type'], $format, $context);
            $data = $this->objectNormalizer->denormalize($data['_value'], $data['_type'], $format, $context);

            return $data;
        }

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->serializer->denormalize($value, $class, $format, $context);
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        if ($this->objectNormalizer instanceof SerializerAwareInterface) {
            $this->objectNormalizer->setSerializer($serializer);
        }
    }
}
