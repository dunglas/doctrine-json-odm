<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\AppKernelWithTypeMap;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Attribute;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Attributes;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Bar;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Baz;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\ScalarValue;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\WithMappedType;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Foo;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class SerializerTest extends AbstractKernelTestCase
{
    public function testStoreAndRetrieveDocument(): void
    {
        $attribute1 = new Attribute();
        $attribute1->key = 'foo';
        $attribute1->value = 'bar';

        $attribute2 = new Attribute();
        $attribute2->key = 'weights';
        $attribute2->value = [34, 67];

        $attributes = [$attribute1, $attribute2];

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize($attributes, 'json');
        $restoredAttributes = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($attributes, $restoredAttributes);
    }

    public function testStoreAndRetrieveDocumentsOfVariousTypes(): void
    {
        $bar = new Bar();
        $bar->setTitle('Bar');
        $bar->setWeight(12);

        $baz = new Baz();
        $baz->setName('Baz');
        $baz->setSize(7);

        $scalarValue = new ScalarValue('foobar');

        $misc = [$bar, $baz, $scalarValue];

        $foo = new Foo();
        $foo->setName('Foo');
        $foo->setMisc($misc);

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize($misc, 'json');
        $restoredMisc = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($misc, $restoredMisc);
    }

    public function testNestedObjects(): void
    {
        $attribute = new Attribute();
        $attribute->key = 'nested';
        $attribute->value = 'bar';

        $attributeParent = new Attribute();
        $attributeParent->key = 'parent';
        $attributeParent->value = [[$attribute]];

        $misc = [$attributeParent];

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize($misc, 'json');
        $restoredMisc = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($misc, $restoredMisc);
    }

    public function testNestedObjectsInNestedObject(): void
    {
        $attribute1 = new Attribute();
        $attribute1->key = 'attribute1';

        $attribute2 = new Attribute();
        $attribute2->key = 'attribute2';

        $attributes = new Attributes();
        $attributes->setAttributes([$attribute1, $attribute2]);

        $misc = [$attributes];

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize($misc, 'json');
        $restoredMisc = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($misc, $restoredMisc);
    }

    public function testNullIsStoredAsNull(): void
    {
        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize(null, 'json');
        $restoredMisc = $serializer->deserialize($data, '', 'json');

        $this->assertEquals(null, $restoredMisc);
    }

    public function testScalarIsStoredInScalarKey(): void
    {
        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $value = new ScalarValue('foobar');
        $data = $serializer->serialize($value, 'json');
        $decodeData = json_decode($data, true);
        $this->assertArrayHasKey('#scalar', $decodeData);
        $this->assertSame($value->value(), $decodeData['#scalar']);
        $restoredValue = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($value, $restoredValue);
    }

    public function testTypeMappingIsOptional(): void
    {
        $this->assertFalse(self::$kernel->getContainer()->get('test.service_container')->has('dunglas_doctrine_json_odm.type_mapper'));

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');

        $value = new WithMappedType();
        $data = $serializer->serialize($value, 'json');
        $decodeData = json_decode($data, true);
        $this->assertArrayHasKey('#type', $decodeData);
        $this->assertSame(WithMappedType::class, $decodeData['#type']);
        $restoredValue = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($value, $restoredValue);
    }

    public function testTypeIsMappedFromConfig(): void
    {
        $this->useTypeMapConfig();

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');

        $value = new WithMappedType();
        $data = $serializer->serialize($value, 'json');
        $decodeData = json_decode($data, true);
        $this->assertArrayHasKey('#type', $decodeData);
        $this->assertSame('mappedType', $decodeData['#type']);
        $restoredValue = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($value, $restoredValue);
    }

    public function testClassNameAlsoWorksForMappedTypes(): void
    {
        $this->useTypeMapConfig();

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');

        $value = new WithMappedType();
        $serialized = json_encode([
            '#type' => WithMappedType::class,
            'foo' => 'bar',
        ]);

        $restoredValue = $serializer->deserialize($serialized, '', 'json');

        $this->assertEquals($value, $restoredValue);
    }

    private function useTypeMapConfig(): void
    {
        self::ensureKernelShutdown();
        self::$class = AppKernelWithTypeMap::class;
        self::bootKernel();

        $this->assertTrue(self::$kernel->getContainer()->get('test.service_container')->has('dunglas_doctrine_json_odm.type_mapper'));
    }
}
