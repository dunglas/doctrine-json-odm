# Doctrine JSON ODM

An ODM for Doctrine ORM (!) using JSON types of modern RDBMS.
It allows to store and queries graphs of PHP objects (objects and objects related to other objects) as JSON documents.
With modern databases (PostgreSQL >= 9.4 and, soon, MySQL >= 5.7.8).

It enables the possibility to create powerful data models mixing traditional relational mappings with modern
schema-less and NOSQL-like ones.

## Install

If you use [Symfony](https://symfony.com), just install the bundle provided by the library.
The doc to use the library standalone has not be started at this time.

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

The library provide a new `json_document` type to map Doctrine properties. The content of such properties will be transformed
in JSON using the Symfony Serializer and stored in a JSON column in the database.
Later, when the object will be hydrated, the content JSON of this column will be transformed back to its original values.
All PHP objects and structures will be preserved.

You can store any type of PHP data structures in properties mapped with the `json_document` type.

Exemple:


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

Yes. First, need Postgres >= 9.4 as well as Doctrine ORM and DBAL >= 2.6 (dev).
Then, set the column option of your field to use JSONB:

```php
// ...

    /**
     * @ORM\Column(type="json_document", options={"jsonb": true})
     */
    public $foo;

// ...
```

**How to store objects recursively in the ODM?**

If you want to store an object graph with the ODM, you need to install Symfony >= 3.1 (dev).
The `phpdocumentor/reflection-docblock` package must also be installed in your application.
Finally, you need to describe your properties containing object with an accurate PHPDoc.

The serializer used by the ODM will use this PHPDoc to normalize the object graph.

Internally, it uses [the Symfony PropertyInfo component](http://symfony.com/blog/new-in-symfony-2-8-propertyinfo-component).

## Run tests

Run the following commands in your shell to set mandatory environment variables:

    export SYMFONY__POSTGRESQL_HOST=127.0.0.1
    export SYMFONY__POSTGRESQL_USER=dunglas
    export SYMFONY__POSTGRESQL_PASSWORD=
    export SYMFONY__POSTGRESQL_DBNAME=mytestdatabase

The database must exist. Be careful, it contents may be erased.

Run the test suite using [PHPUnit](https://phpunit.de/):

    phpunit

The database is automatically created and dropped.

## Credits

This bundle is brought to you by [Kévin Dunglas](https://dunglas.fr) and [awesome contributors](https://github.com/dunglas/DunglasActionBundle/graphs/contributors).
Sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
