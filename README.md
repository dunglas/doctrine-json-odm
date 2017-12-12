# Doctrine JSON ODM

An Object-Document Mapper (ODM) for [Doctrine ORM](http://www.doctrine-project.org/projects/orm.html) leveraging new JSON types of modern RDBMS.

[![Build Status](https://travis-ci.org/dunglas/doctrine-json-odm.svg?branch=master)](https://travis-ci.org/dunglas/doctrine-json-odm)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dunglas/doctrine-json-odm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dunglas/doctrine-json-odm/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/20cf915b-1554-4f89-8772-ef0f913ec759/mini.png)](https://insight.sensiolabs.com/projects/20cf915b-1554-4f89-8772-ef0f913ec759)
[![StyleCI](https://styleci.io/repos/57223826/shield)](https://styleci.io/repos/57223826)

Did you ever dreamed of a tool creating powerful data models mixing traditional efficient relational mappings with modern
schema-less and NoSQL-like ones?

With Doctrine JSON ODM, it's now possible to create and query such hybrid data models with ease. Thanks to [modern JSON
types of RDBMS](http://www.postgresql.org/docs/current/static/datatype-json.html), querying schema-less documents is easy,
powerful and [fast as hell (similar in performance to a MongoDB database)](http://www.enterprisedb.com/postgres-plus-edb-blog/marc-linster/postgres-outperforms-mongodb-and-ushers-new-developer-reality)!
You can even [define indexes](http://www.postgresql.org/docs/current/static/datatype-json.html#JSON-INDEXING) for those documents.

Doctrine JSON ODM allows to store PHP objects as JSON documents in modern dynamic columns of RDBMS.
It works with JSON and JSONB columns of PostgreSQL (>= 9.4) and the JSON column of MySQL (>= 5.7.8).

For more information about concepts behind Doctrine JSON ODM, take a look at [the presentation given by Benjamin Eberlei at Symfony Catalunya 2016](https://qafoo.com/resources/presentations/symfony_catalunya_2016/doctrine_orm_and_nosql.html).

## Install

If you are using [Symfony](https://symfony.com) or [API Platform](https://api-platform.com), jump to the next section to learn how to install the provided bundle.
If you use Doctrine directly, use a bootstrap code similar to the following:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php'; // Adapt to your path

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dunglas\DoctrineJsonOdm\Normalizer\ObjectNormalizer;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as BaseObjectNormalizer;

if (!Type::hasType('json_document')) {
    Type::addType('json_document', JsonDocumentType::class);
    Type::getType('json_document')->setSerializer(
        new Serializer([new ObjectNormalizer(new BaseObjectNormalizer())], [new JsonEncoder()])
    );
}

// Sample bootstrapping code here, adapt to fit your needs
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . "/../src"], $_ENV['DEBUG'] ?? false); // Adapt to your path

$conn = [
    'dbname' => $_ENV['DATABASE_NAME'],
    'user' => $_ENV['DATABASE_USER'],
    'password' => $_ENV['DATABASE_PASSWORD'],
    'host' => $_ENV['DATABASE_HOST'],
    'driver' => 'pdo_mysql' // or pdo_pgsql
];

return EntityManager::create($conn, $config);
```

## Install with Symfony

The library comes with a bundle for the [Symfony](https://symfony.com) framework.

```php
// ...

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Dunglas\DoctrineJsonOdm\Bundle\DunglasDoctrineJsonOdmBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DunglasDoctrineJsonOdmBundle(),

            // ...
        ];
    }

    // ...
}
```

## Usage

Doctrine JSON ODM provides a `json_document` column type for properties of Doctrine entities.

The content of properties mapped with this type is serialized in JSON using the [Symfony Serializer](http://symfony.com/doc/current/components/serializer.html)
then, it is stored in a dynamic JSON column in the database.

When the object will be hydrated, the JSON content of this column is transformed back to its original values, thanks again
to the Symfony Serializer.
All PHP objects and structures will be preserved (if you use Symfony >= 3.1, see the FAQ).

You can store any type of (serializable) PHP data structures in properties mapped using the `json_document` type.

Example:

```php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This is a typical Doctrine ORM entity.
 *
 * @ORM\Entity
 */
class Foo
{
  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  public $id;

  /**
   * @ORM\Column(type="string")
   */
  public $name;

  /**
   * Can contain anything (array, objects, nested objects...).
   *
   * @ORM\Column(type="json_document", options={"jsonb": true})
   */
  public $misc;

  // Works with private and protected methods with getters and setters too.
}
```

```php
namespace AppBundle\Entity;

/**
 * This is NOT an entity! It's a POPO (Plain Old PHP Object). It can contain anything.
 */
class Bar
{
    public $title;
    public $weight;
}
```

```php
namespace AppBundle\Entity;

/**
 * This is NOT an entity. It's another POPO and it can contain anything.
 */
class Baz
{
    public $name;
    public $size;
}
```

Store a graph of random object in the JSON type of the database:

```php
// $entityManager = $this->get('doctrine')->getManagerForClass(AppBundle\Entity\Foo::class);

$bar = new Bar();
$bar->title = 'Bar';
$bar->weight = 12;

$baz = new Baz();
$baz->name = 'Baz';
$baz->size = 7;

$foo = new Foo();
$foo->name = 'Foo';
$foo->misc = [$bar, $baz]

$entityManager->persist($foo);
$entityManager->flush();
```

Retrieve the object graph back:

```php
$foo = $entityManager->find(Foo::class, $foo->getId());
var_dump($foo->misc); // Same as what we set earlier
```

You can execute complex queries using [native queries](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/native-sql.html).
Checkout [the PostgreSQL documentation](http://www.postgresql.org/docs/current/static/datatype-json.html) or [the MySQL](https://dev.mysql.com/doc/refman/en/json.html)
one to learn how to query the stored JSON document.

## FAQ

**What DBMS are supported?**

PostgreSQL 9.4+ and MySQL 5.7+ are supported.

**Which versions of Doctrine are supported?**

Doctrine ORM 2.6+ and DBAL 2.6+ are supported.

**How to use [the JSONB type of PostgreSQL](http://www.postgresql.org/docs/current/static/datatype-json.html)?**

Then, you need to set an option of in the column mapping:

```php
// ...

    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     */
    public $foo;

// ...
```

**Does the ODM support nested objects and object graphs?**

Yes.

## Run tests

To execute the test suite, you need running PostgreSQL and MySQL servers.
Run the following commands in your shell to set mandatory environment variables:

    export POSTGRESQL_HOST=127.0.0.1
    export POSTGRESQL_USER=dunglas
    export POSTGRESQL_PASSWORD=
    export POSTGRESQL_DBNAME=my_test_db

    export MYSQL_HOST=127.0.0.1
    export MYSQL_USER=dunglas
    export MYSQL_PASSWORD=
    export MYSQL_=DBNAME="my_test_db

Databases must exist. Be careful, its content may be deleted.

Run the test suite, execute [PHPUnit](https://phpunit.de/):

    phpunit

## Credits

This bundle is brought to you by [Kévin Dunglas](https://dunglas.fr) and [awesome contributors](https://github.com/dunglas/doctrine-json-odm/graphs/contributors).
Sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
