<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\Tests;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Attribute;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Attributes;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Bar;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\Baz;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document\ScalarValue;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Foo;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Product;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Enum\InputMode;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Uid\Uuid;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class FunctionalTest extends AbstractKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runCommand('doctrine:schema:drop --force');
        $this->runCommand('doctrine:schema:create');
    }

    private function runCommand($command): void
    {
        $this->application->run(new StringInput($command.' --no-interaction --quiet'));
    }

    public function testStoreAndRetrieveDocument(): void
    {
        $attribute1 = new Attribute();
        $attribute1->key = 'foo';
        $attribute1->value = 'bar';

        $attribute2 = new Attribute();
        $attribute2->key = 'weights';
        $attribute2->value = [34, 67];

        $attributes = [$attribute1, $attribute2];

        $product = new Product();
        $product->name = 'My product';
        $product->attributes = $attributes;

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Product::class);
        $manager->persist($product);
        $manager->flush();

        $manager->clear();

        $retrievedProduct = $manager->find(Product::class, $product->id);
        $this->assertEquals($attributes, $retrievedProduct->attributes);
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

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Foo::class);
        $manager->persist($foo);
        $manager->flush();

        $manager->clear();

        $foo = $manager->find(Foo::class, $foo->getId());
        $this->assertEquals($misc, $foo->getMisc());
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

        $foo = new Foo();
        $foo->setName('foo');
        $foo->setMisc($misc);

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Foo::class);
        $manager->persist($foo);
        $manager->flush();

        $manager->clear();

        $foo = $manager->find(Foo::class, $foo->getId());
        $this->assertEquals($misc, $foo->getMisc());
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

        $foo = new Foo();
        $foo->setName('foo');
        $foo->setMisc($misc);

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Foo::class);

        $manager->persist($foo);
        $manager->flush();
        $manager->clear();

        $foo = $manager->find(Foo::class, $foo->getId());

        $this->assertEquals($misc, $foo->getMisc());
    }

    public function testNullIsStoredAsNull(): void
    {
        $product = new Product();
        $product->name = 'My product';
        $product->attributes = null;

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Product::class);
        $manager->persist($product);
        $manager->flush();
        $manager->clear();

        $connection = $manager->getConnection();
        $statement = $connection->executeQuery('SELECT * FROM Product');
        $this->assertNull($statement->fetchAssociative()['attributes']);
    }

    public function testStoreAndRetrieveDocumentWithInstantiatedOtherSerializer(): void
    {
        /**
         * This call is necessary to cover this issue.
         *
         * @see https://github.com/dunglas/doctrine-json-odm/pull/78
         */
        $serializer = self::$kernel->getContainer()->get('serializer');

        $attribute1 = new Attribute();
        $attribute1->key = 'foo';
        $attribute1->value = 'bar';

        $attribute2 = new Attribute();
        $attribute2->key = 'weights';
        $attribute2->value = [34, 67];

        $attributes = [$attribute1, $attribute2];

        $product = new Product();
        $product->name = 'My product';
        $product->attributes = $attributes;

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Product::class);
        $manager->persist($product);
        $manager->flush();

        $manager->clear();

        $retrievedProduct = $manager->find(Product::class, $product->id);
        $this->assertEquals($attributes, $retrievedProduct->attributes);
    }

    /**
     * @requires PHP >= 8.1
     * @requires function \Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer::normalize
     */
    public function testStoreAndRetrieveEnum(): void
    {
        $attribute = new Attribute();
        $attribute->key = 'email';
        $attribute->value = InputMode::EMAIL;

        $product = new Product();
        $product->name = 'My product';
        $product->attributes = [$attribute];

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Product::class);
        $manager->persist($product);
        $manager->flush();

        $manager->clear();

        $retrievedProduct = $manager->find(Product::class, $product->id);

        $this->assertSame(InputMode::EMAIL, $retrievedProduct->attributes[0]->value);
    }

    /**
     * @requires PHP >= 7.2
     * @requires function \Symfony\Component\Serializer\Normalizer\UidNormalizer::normalize
     */
    public function testStoreAndRetrieveUid(): void
    {
        $uuid = '1a87e1f2-1569-4493-a4a8-bc1915ca5631';

        $attribute = new Attribute();
        $attribute->key = 'uid';
        $attribute->value = Uuid::fromString($uuid);

        $product = new Product();
        $product->name = 'My product';
        $product->attributes = [$attribute];

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Product::class);
        $manager->persist($product);
        $manager->flush();

        $manager->clear();

        $retrievedProduct = $manager->find(Product::class, $product->id);

        $this->assertInstanceOf(Uuid::class, $retrievedProduct->attributes[0]->value);
        $this->assertEquals($uuid, (string) $retrievedProduct->attributes[0]->value);
    }
}
