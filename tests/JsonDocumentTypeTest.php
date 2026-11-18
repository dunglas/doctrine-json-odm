<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The serializer can be given to the type itself or to every instance of it at once: Doctrine, not
 * the caller, creates the instance a connection uses.
 */
final class JsonDocumentTypeTest extends TestCase
{
    protected function tearDown(): void
    {
        $sharedSerializer = new \ReflectionProperty(JsonDocumentType::class, 'sharedSerializer');
        $sharedSerializer->setValue(null, null);
    }

    public function testItUsesTheSharedSerializerWhenItHasNoneOfItsOwn(): void
    {
        JsonDocumentType::setSharedSerializer($this->serializer('shared'));

        self::assertSame('shared', (new JsonDocumentType())->convertToDatabaseValue(new \stdClass(), $this->platform()));
    }

    public function testItsOwnSerializerWinsOverTheSharedOne(): void
    {
        JsonDocumentType::setSharedSerializer($this->serializer('shared'));

        $type = new JsonDocumentType();
        $type->setSerializer($this->serializer('own'));

        self::assertSame('own', $type->convertToDatabaseValue(new \stdClass(), $this->platform()));
    }

    public function testItFailsWhenNeitherSerializerWasProvided(): void
    {
        $this->expectException(\RuntimeException::class);

        (new JsonDocumentType())->convertToDatabaseValue(new \stdClass(), $this->platform());
    }

    private function serializer(string $returns): SerializerInterface
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('serialize')->willReturn($returns);

        return $serializer;
    }

    private function platform(): AbstractPlatform
    {
        return $this->createMock(AbstractPlatform::class);
    }
}
