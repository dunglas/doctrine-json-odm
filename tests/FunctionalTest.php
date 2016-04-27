<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm\tests;

use Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Entity\Attribute;
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

        $this->runCommand('doctrine:database:create');
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
}
