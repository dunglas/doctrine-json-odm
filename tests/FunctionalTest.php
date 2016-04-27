<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\tests;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Attribute;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Bar;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Baz;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Foo;
use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\StringInput;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class FunctionalTest extends KernelTestCase
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

        $this->runCommand('doctrine:schema:drop --force');
        $this->runCommand('doctrine:schema:create');
    }

    private function runCommand($command)
    {
        return $this->application->run(new StringInput($command.' --no-interaction'));
    }

    public function testStoreAndRetrieveDocument()
    {
        $attribute1 = new Attribute();
        $attribute1->key = 'foo';
        $attribute1->value = 'bar';

        $attribute2 = new Attribute();
        $attribute2->key = 'weights';
        $attribute2->value = [34, 67];

        $product = new Product();
        $product->name = 'My product';
        $product->attributes = [$attribute1, $attribute2];

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Product::class);
        $manager->persist($product);
        $manager->flush();

        $manager->clear();

        $retrievedProduct = $manager->find(Product::class, $product->id);
        $this->assertCount(2, $retrievedProduct->attributes);
        $this->assertEquals('foo', $retrievedProduct->attributes[0]->key);
        $this->assertEquals('bar', $retrievedProduct->attributes[0]->value);
        $this->assertEquals('weights', $retrievedProduct->attributes[1]->key);
        $this->assertEquals([34, 67], $retrievedProduct->attributes[1]->value);
    }

    public function testStoreAndRetrieveDocumentsOfVariousTypes()
    {
        $bar = new Bar();
        $bar->setTitle('Bar');
        $bar->setWeight(12);

        $baz = new Baz();
        $baz->setName('Baz');
        $baz->setSize(7);

        $foo = new Foo();
        $foo->setName('Foo');
        $foo->setMisc([$bar, $baz]);

        $manager = self::$kernel->getContainer()->get('doctrine')->getManagerForClass(Foo::class);
        $manager->persist($foo);
        $manager->flush();

        $manager->clear();

        $manager->find(Foo::class, $foo->getId());
        $this->assertInstanceOf(Bar::class, $foo->getMisc()[0]);
        $this->assertEquals('Bar', $foo->getMisc()[0]->getTitle());
        $this->assertEquals(12, $foo->getMisc()[0]->getWeight());
        $this->assertInstanceOf(Baz::class, $foo->getMisc()[1]);
        $this->assertEquals('Baz', $foo->getMisc()[1]->getName());
        $this->assertEquals(7, $foo->getMisc()[1]->getSize());
    }
}
