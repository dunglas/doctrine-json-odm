<?php

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\Normalizer;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Vector;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Notice that normally you have to call (de-)normalize() for all nested objects graph (like array items in this case).
 * But, this package ships its own serializer that will do this for you :)
 * Vanilla normalizers that DOES call to normalize() for nested objects are fine too, they won't break.
 */
final class VectorNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [
            Vector::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$this->supportsNormalization($object)) {
            throw new InvalidArgumentException(sprintf('The object must be an instance of "%s".', Vector::class));
        }

        return ['position' => $object->key(), '[]' => $object->getArray()];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Vector;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): object
    {
        if (!$this->supportsDenormalization($data, $type)) {
            throw NotNormalizableValueException::createForUnexpectedDataType('Data expected to be a array of shape {"position": int, "[]": array}.', $data, ['array'], $context['deserialization_path'] ?? null);
        }

        return new Vector($data['[]'], $data['position']);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_array($data) && is_a($type, Vector::class, true);
    }
}
