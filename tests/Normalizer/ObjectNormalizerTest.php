<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\tests\Normalizer;

use Dunglas\DoctrineJsonOdm\Normalizer\ObjectNormalizer;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\ScalarValue;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
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
