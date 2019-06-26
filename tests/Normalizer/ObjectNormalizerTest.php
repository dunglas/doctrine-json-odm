<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\tests\Normalizer;

use Dunglas\DoctrineJsonOdm\Normalizer\ObjectNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class ObjectNormalizerTest extends TestCase
{
    public function testObjectToScalarNormalization()
    {
        $normalizer = new ObjectNormalizer(new CustomNormalizer());
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        $data = $serializer->normalize(new ScalarValue('foobar'));
        $this->assertArrayHasKey('#type', $data);
        $this->assertArrayHasKey('#scalar', $data);

        $object = $serializer->denormalize($data, ScalarValue::class);
        $this->assertInstanceOf(ScalarValue::class, $object);
        $this->assertSame('foobar', $object->value());
    }
}

class ScalarValue implements NormalizableInterface, DenormalizableInterface
{
    private $value;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function normalize(NormalizerInterface $normalizer, $format = null, array $context = [])
    {
        return $this->value;
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = [])
    {
        $this->value = $data;

        return $this;
    }
}
