<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonArrayType;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * The JSON document type.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class JsonDocumentType extends JsonArrayType
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var string
     */
    private $format = 'json';

    /**
     * @var array
     */
    private $normalizationContext = [];

    /**
     * @var array
     */
    private $denormalizationContext = [];

    /**
     * @var PropertyTypeExtractorInterface
     */
    private $propertyTypeExtractor;

    /**
     * Sets the serializer to use.
     *
     * @param NormalizerInterface $normalizer
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Gets the serializer or throw an exception if it isn't available.
     *
     * @return NormalizerInterface
     *
     * @throws \RuntimeException
     */
    private function getNormalizer()
    {
        if (null === $this->normalizer) {
            throw new \RuntimeException(sprintf('An instance of "%s" must be available. Call the "setNormalizer" method.', NormalizerInterface::class));
        }

        return $this->normalizer;
    }

    /**
     * Sets the serialization format (default to "json").
     *
     * @param string $format
     */
    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    /**
     * Sets the serialization context (default to an empty array).
     *
     * @param array $normalizationContext
     */
    public function setNormalizationContext(array $normalizationContext)
    {
        $this->normalizationContext = $normalizationContext;
    }

    /**
     * Sets the deserialization context (default to an empty array).
     *
     * @param array $denormalizationContext
     */
    public function setDenormalizationContext(array $denormalizationContext)
    {
        $this->denormalizationContext = $denormalizationContext;
    }

    /**
     * Sets the PropertyTypeExtractor to use.
     */
    public function setPropertyTypeExtractor(PropertyTypeExtractorInterface $propertyTypeExtractor)
    {
        $this->propertyTypeExtractor = $propertyTypeExtractor;
    }

    /**
     * Gets the PropertyTypeExtractor or throw an exception if it isn't available.
     *
     * @return PropertyTypeExtractorInterface
     *
     * @throws \RuntimeException
     */
    private function getPropertyTypeExtractor()
    {
        if (null === $this->propertyTypeExtractor) {
            throw new \RuntimeException(sprintf('An instance of "%s" must be available. Call the "setNormalizer" method.', PropertyTypeExtractorInterface::class));
        }

        return $this->propertyTypeExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $this->getNormalizer()->serialize($value, $this->format, $this->normalizationContext);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!isset($value['value']) || !isset($value['class']) || !isset($value['property'])) {
            throw new \RuntimeException('The value passed is invalid. Are you using a custom hydrator?');
        }

        $serializer = $this->getNormalizer();
        $propertyTypeExtractor = $this->getPropertyTypeExtractor();

        $types = $propertyTypeExtractor->getTypes($value['class'], $value['property']);
        if (!isset($types[0])) {
            // Throw an exception here instead?
            return $serializer->decode($value['value'], $this->format);
        }

        $type = $types[0];

        if (Type::BUILTIN_TYPE_OBJECT == $type->getBuiltinType() && $className = $type->getClassName()) {
            return $serializer->deserialize($value['value'], $className, $this->format, $this->denormalizationContext);
        }

        // It doesn't need to be recursive, PHPDoc doesn't allow to define subtypes
        if (
            $type->isCollection() &&
            ($collectionValueType = $type->getCollectionValueType()) &&
            (Type::BUILTIN_TYPE_OBJECT == $collectionValueType->getBuiltinType()) &&
            $className = $collectionValueType->getClassName()
        ) {
            $value = $serializer->decode($value['value'], $this->format);
            $data = [];
            foreach ($value as $k => $v) {
                $data[$k] = $serializer->denormalize($v, $className, $this->format, $this->denormalizationContext);
            }

            return $data;
        }

        return $serializer->decode($value['value'], $this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'json_document';
    }
}
