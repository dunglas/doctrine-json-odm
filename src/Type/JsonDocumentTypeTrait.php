<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Common serialization logic for JSON document types.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
trait JsonDocumentTypeTrait
{
    /**
     * Shared by every instance of the type, so the serializer can be provided once even when the
     * instance is created by Doctrine rather than by the caller.
     */
    private static ?SerializerInterface $sharedSerializer = null;

    private ?SerializerInterface $serializer = null;

    private string $format = 'json';

    private array $serializationContext = [];

    private array $deserializationContext = [];

    /**
     * Sets the serializer to use.
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * Sets the serializer used by every instance of this type that was not given one of its own.
     */
    public static function setSharedSerializer(SerializerInterface $serializer): void
    {
        self::$sharedSerializer = $serializer;
    }

    /**
     * Gets the serializer or throw an exception if it isn't available.
     *
     * @throws \RuntimeException
     */
    private function getSerializer(): SerializerInterface
    {
        if (null === $serializer = $this->serializer ?? self::$sharedSerializer) {
            throw new \RuntimeException(sprintf('An instance of "%s" must be available. Call the "setSerializer" or "setSharedSerializer" method.', SerializerInterface::class));
        }

        return $serializer;
    }

    /**
     * Sets the serialization format (default to "json").
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Sets the serialization context (default to an empty array).
     */
    public function setSerializationContext(array $serializationContext): void
    {
        $this->serializationContext = $serializationContext;
    }

    /**
     * Sets the deserialization context (default to an empty array).
     */
    public function setDeserializationContext(array $deserializationContext): void
    {
        $this->deserializationContext = $deserializationContext;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return $this->getSerializer()->serialize($value, $this->format, $this->serializationContext);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value || $value === '') {
            return null;
        }

        return $this->getSerializer()->deserialize($value, '', $this->format, $this->deserializationContext);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
