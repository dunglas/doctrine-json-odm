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
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $format = 'json';

    /**
     * @var array
     */
    private $serializationContext = [];

    /**
     * @var array
     */
    private $deserializationContext = [];

    /**
     * Sets the serializer to use.
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Sets the serialization format (default to "json").
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Sets the serialization context (default to an empty array).
     *
     * @param array $serializationContext
     */
    public function setSerializationContext(array $serializationContext)
    {
        $this->serializationContext = $serializationContext;
    }

    /**
     * Sets the deserialization context (default to an empty array).
     *
     * @param array $deserializationContext
     */
    public function setDeserializationContext(array $deserializationContext)
    {
        $this->deserializationContext = $deserializationContext;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return;
        }

        return $this->getSerializer()->serialize($value, $this->format, $this->serializationContext);
    }

    /**
     * Gets the serializer or throw an exception if it isn't available.
     *
     * @return SerializerInterface
     * @throws \RuntimeException
     *
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            throw new \RuntimeException(
                sprintf(
                    'An instance of "%s" must be available. Call the "setSerializer" method.',
                    SerializerInterface::class
                )
            );
        }

        return $this->serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || $value === '') {
            return;
        }

        return $this->getSerializer()->deserialize($value, '', $this->format, $this->deserializationContext);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'json_document';
    }
}
