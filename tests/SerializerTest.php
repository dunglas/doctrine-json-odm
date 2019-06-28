<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\tests;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Attribute;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Attributes;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Bar;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Baz;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Foo;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class SerializerTest extends KernelTestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp()
    {
        $this->bootKernel();

        $this->application = new Application(self::$kernel);
        $this->application->setAutoExit(false);
    }

    public function testStoreAndRetrieveDocument()
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

    public function testStoreAndRetrieveDocumentsOfVariousTypes()
    {
        $bar = new Bar();
        $bar->setTitle('Bar');
        $bar->setWeight(12);

        $baz = new Baz();
        $baz->setName('Baz');
        $baz->setSize(7);

        $misc = [$bar, $baz];

        $foo = new Foo();
        $foo->setName('Foo');
        $foo->setMisc($misc);

        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize($misc, 'json');
        $restoredMisc = $serializer->deserialize($data, '', 'json');

        $this->assertEquals($misc, $restoredMisc);
    }

    public function testNestedObjects()
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

    public function testNestedObjectsInNestedObject()
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

    public function testNullIsStoredAsNull()
    {
        $serializer = self::$kernel->getContainer()->get('dunglas_doctrine_json_odm.serializer');
        $data = $serializer->serialize(null, 'json');
        $restoredMisc = $serializer->deserialize($data, '', 'json');

        $this->assertEquals(null, $restoredMisc);
    }
}
