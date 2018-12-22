<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Normalizer;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Transforms an object to an array.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class ObjectNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    // To not use the current normalizer
    const WONT_DENORMALIZE = 'dunglas_normalizer_wont_denormalize';
    const TYPE_FIELD = '#type';

    /**
     * @var NormalizerInterface|DenormalizerInterface
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
        return \array_merge([self::TYPE_FIELD => ClassUtils::getClass($object)], $this->objectNormalizer->normalize($object, $format, $context));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return \is_object($data);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!\is_iterable($data) || isset($context[self::WONT_DENORMALIZE]) || \is_object($data)) {
            return $data;
        }

        if (null !== $type = $this->extractType($data)) {
            return $this->denormalizeObject($data, $type, $format, $context);
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->denormalizeValue($value, $class, $format, $context);
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
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(
                sprintf('The injected serializer must implement "%s" and "%s".', NormalizerInterface::class, DenormalizerInterface::class)
            );
        }

        $this->serializer = $serializer;

        if ($this->objectNormalizer instanceof SerializerAwareInterface) {
            $this->objectNormalizer->setSerializer($serializer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Converts data to $class' object if possible.
     *
     * @param array  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return object|null
     */
    private function denormalizeObject(array $data, string $class, $format = null, array $context = [])
    {
    	// Try to denormalize in other normalizers. Necessary to avoid cycle
	    $context[self::WONT_DENORMALIZE] = true;
	    if (\is_object($denormalizedValue = $this->serializer->denormalize($data, $class, $format, $context))) {
	    	return $denormalizedValue;
	    }

	    // Denormalize in current default normalizer
	    unset($context[self::WONT_DENORMALIZE]);

	    foreach ($data as $key => $value) {
		    $data[$key] = $this->denormalizeValue($value, $class, $format, $context);
	    }

	    return $this->objectNormalizer->denormalize($data, $class, $format, $context);
    }

    /**
     * Convert raw value to normalized value - object or primitive type.
     *
     * @param mixed  $value
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return object|null
     */
    private function denormalizeValue($value, string $class, $format = null, array $context = [])
    {
        if (\is_object($value)) {
            return $value;
        }

        return (null !== $type = $this->extractType($value))
            ? $this->denormalizeObject($value, $type, $format, $context)
            : $this->serializer->denormalize($value, $class, $format, $context);
    }

    /**
     * Grab class from array.
     *
     * @param $data
     *
     * @return string|null
     */
    private function extractType(&$data)
    {
        if (!isset($data[self::TYPE_FIELD])) {
            return null;
        }

        $type = $data[self::TYPE_FIELD];
        unset($data[self::TYPE_FIELD]);

        return $type;
    }
}
