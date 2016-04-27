# Doctrine JSON ODM

An Object-Document Mapper (ODM) for Doctrine ORM leveraging new JSON types of modern RDBMS.

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
It works with JSON and JSONB columns of PostgreSQL (>= 9.4) and, soon, will support the JSON column of MySQL (>= 5.7.8).

## Install

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

Doctrine JSON ODM can also be used standalone, without any framework, but hasn't been redacted yet (PRs welcome!).

## Usage

Doctrine JSON ODM provides a `json_document` column type for properties of Doctrine entities.

The content of properties mapped with this type is serialized in JSON using the [Symfony Serializer](http://symfony.com/doc/current/components/serializer.html)
then, it is stored in a dynamic JSON column in the database.

When the object will be hydrated, the JSON content of this column is transformed back to its original values, thanks again
to the Symfony Serializer.
All PHP objects and structures will be preserved (if you use Symfony >= 3.1, see thr FAQ).

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
  private $id;

  /**
   * @ORM\Column(type="string")
   */
  private $name;

  /**
   * @ORM\Column(type="json_document", options={"jsonb": true})
   */
  private $misc;

  public function getId()
  {
      return $this->id;
  }

  public function getName()
  {
      return $this->name;
  }

  public function setName($name)
  {
      $this->name = $name;
  }

  public function getMisc()
  {
      return $this->misc;
  }

  public function setMisc(array $misc)
  {
      $this->misc = $misc;
  }
}
```

```php
namespace AppBundle\Entity;

/**
 * This is NOT an entity! It's a POPO.
 */
class Bar
{
    private $title;
    private $weight;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
}
```

```php
namespace AppBundle\Entity;

/**
 * This is NOT an entity. It's another POPO.
 */
class Baz
{
    private $name;
    private $size;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }
}
```

Store a graph of random object in the JSON type of the database:

```php
// $entityManager = $this->get('doctrine'))->get('doctrine')->getManagerForClass(\AppBundle/EntityFoo::class);

$bar = new Bar();
$bar->setTitle('Bar');
$bar->setWeight(12);

$baz = new Baz();
$baz->setName('Baz');
$baz->setSize(7);

$foo = new Foo();
$foo->setName('Foo');
$foo->setMisc([$bar, $baz]);

$entityManager->persist($foo);
$entityManager->flush();
```

Retrieve the object graph back:

```php
$entityManager->find(Foo::class, $foo->getId());
```

You can execute complex queries using [native queries](http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/native-sql.html).
Checkout [the PostgreSQL documentation](http://www.postgresql.org/docs/current/static/datatype-json.html) to learn how to query the stored JSON document.

MySQL support is coming (see the FAQ).

## FAQ

**What DBMS are supported?**

Currently only PostgreSQL is supported.
MySQL (>= 5.7.8) will be supported when [the Pull Request doctrine/dbal#2266](https://github.com/doctrine/dbal/pull/2266) will be merged.

**How to use [the JSONB type of PostgreSQL](http://www.postgresql.org/docs/current/static/datatype-json.html)?**

First, be sure to use Postgres >= 9.4, Doctrine ORM >= 2.6 (dev) and DBAL >= 2.6 (dev).
Then, you need to set an option of in the column mapping:

```php
// ...

    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     */
    public $foo;

// ...
```

**How to store objects recursively in the ODM?**

If you want to store an object graph with Doctrine JSON ODM, you need to install Symfony >= 3.1 (dev).
The `phpdocumentor/reflection-docblock` package is also required.
Finally, you need to describe your properties containing object with an accurate PHPDoc.

The serializer used by the Doctrine JSON ODM will rely on this PHPDoc to normalize the object graph.

Internally, it uses [the Symfony PropertyInfo component](http://symfony.com/blog/new-in-symfony-2-8-propertyinfo-component).

## Run tests

Run the following commands in your shell to set mandatory environment variables:

    export SYMFONY__POSTGRESQL_HOST=127.0.0.1
    export SYMFONY__POSTGRESQL_USER=dunglas
    export SYMFONY__POSTGRESQL_PASSWORD=
    export SYMFONY__POSTGRESQL_DBNAME=mytestdatabase

The database must exist. Be careful, its content may be deleted.

Run the test suite using [PHPUnit](https://phpunit.de/):

    phpunit

## Credits

This bundle is brought to you by [Kévin Dunglas](https://dunglas.fr) and [awesome contributors](https://github.com/dunglas/DunglasActionBundle/graphs/contributors).
Sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
