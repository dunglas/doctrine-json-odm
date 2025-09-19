<?php

namespace Dunglas\DoctrineJsonOdm\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

final class TraversableNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return [
            Traversable::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $result = [];
        foreach (iterator_to_array($object) as $key => $item) {
            $result[$key] = $this->normalizer->normalize($item, $format, $context);
        }
        return $result;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Traversable;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): object
    {
        $result = [];
        foreach ($data as $key => $item) {
            $result[$key] = $this->denormalizer->denormalize($item, $type, $format, $context);
        }
        return new $type($result);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_array($data) && is_subclass_of($type, Traversable::class);
    }
}
