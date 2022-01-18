<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonArrayType;
use Doctrine\DBAL\Types\JsonType;
use Symfony\Component\Serializer\SerializerInterface;

if (class_exists(JsonType::class)) {
    /**
     * @internal
     */
    class InternalParentClass extends JsonType
    {
    }
} else {
    /**
     * @internal
     */
    class InternalParentClass extends JsonArrayType
    {
    }
}

/**
 * The JSON document type.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class JsonDocumentType extends InternalParentClass
{
    private SerializerInterface $serializer;
    private string $format = 'json';

    /**
     * @var array<string, mixed>
     */
    private array $serializationContext = [];

    /**
     * @var array<string, mixed>
     */
    private array $deserializationContext = [];

    /**
     * Sets the serializer to use.
     */
    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * Gets the serializer or throw an exception if it isn't available.
     *
     * @throws \RuntimeException
     */
    private function getSerializer(): SerializerInterface
    {
        if (null === $this->serializer) {
            throw new \RuntimeException(sprintf('An instance of "%s" must be available. Call the "setSerializer" method.', SerializerInterface::class));
        }

        return $this->serializer;
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
     *
     * @param array<string, mixed> $serializationContext
     */
    public function setSerializationContext(array $serializationContext): void
    {
        $this->serializationContext = $serializationContext;
    }

    /**
     * Sets the deserialization context (default to an empty array).
     *
     * @param array<string, mixed> $deserializationContext
     */
    public function setDeserializationContext(array $deserializationContext): void
    {
        $this->deserializationContext = $deserializationContext;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        return $this->getSerializer()->serialize($value, $this->format, $this->serializationContext);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value || $value === '') {
            return null;
        }

        return $this->getSerializer()->deserialize($value, '', $this->format, $this->deserializationContext);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'json_document';
    }
}
