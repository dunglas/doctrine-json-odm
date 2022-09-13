# Doctrine JSON ODM

An Object-Document Mapper (ODM) for [Doctrine ORM](http://www.doctrine-project.org/projects/orm.html) leveraging new JSON types of modern RDBMS.

[![tests](https://github.com/dunglas/doctrine-json-odm/actions/workflows/tests.yml/badge.svg)](https://github.com/dunglas/doctrine-json-odm/actions/workflows/tests.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dunglas/doctrine-json-odm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dunglas/doctrine-json-odm/?branch=master)
[![StyleCI](https://styleci.io/repos/57223826/shield)](https://styleci.io/repos/57223826)

Did you ever dream of a tool creating powerful data models mixing traditional, efficient relational mappings with modern
schema-less and NoSQL-like ones?

With Doctrine JSON ODM, it's now possible to create and query such hybrid data models with ease. Thanks to [modern JSON
types of RDBMS](http://www.postgresql.org/docs/current/static/datatype-json.html), querying schema-less documents is easy,
powerful and [fast as hell (similar in performance to a MongoDB database)](http://www.enterprisedb.com/postgres-plus-edb-blog/marc-linster/postgres-outperforms-mongodb-and-ushers-new-developer-reality)!
You can even [define indexes](http://www.postgresql.org/docs/current/static/datatype-json.html#JSON-INDEXING) for those documents.

Doctrine JSON ODM allows to store PHP objects as JSON documents in modern, dynamic columns of an RDBMS.
It works with JSON and JSONB columns of PostgreSQL (>= 9.4) and the JSON column type of MySQL (>= 5.7.8).

For more information about concepts behind Doctrine JSON ODM, take a look at [the presentation given by Benjamin Eberlei at Symfony Catalunya 2016](https://www.youtube.com/watch?v=E8w1y1Jo7YI).

## Install

To install the library, use [Composer](https://getcomposer.org/), the PHP package manager:

    composer require dunglas/doctrine-json-odm

If you are using [Symfony](https://symfony.com) or [API Platform](https://api-platform.com), you don't need to do anything else!
If you use Doctrine directly, use a bootstrap code similar to the following:

```php
<?php

require_once __DIR__.'/../vendor/autoload.php'; // Adjust to your path

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Dunglas\DoctrineJsonOdm\Serializer;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

if (!Type::hasType('json_document')) {
    Type::addType('json_document', JsonDocumentType::class);
    Type::getType('json_document')->setSerializer(
        new Serializer([new ArrayDenormalizer(), new DateTimeNormalizer(), new ObjectNormalizer()], [new JsonEncoder()])
    );
}

// Sample bootstrapping code here, adapt to fit your needs
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/../src'], $_ENV['DEBUG'] ?? false); // Adapt to your path

$conn = [
    'dbname' => $_ENV['DATABASE_NAME'],
    'user' => $_ENV['DATABASE_USER'],
    'password' => $_ENV['DATABASE_PASSWORD'],
    'host' => $_ENV['DATABASE_HOST'],
    'driver' => 'pdo_mysql' // or pdo_pgsql
];

return EntityManager::create($conn, $config);
```

## Usage

Doctrine JSON ODM provides a `json_document` column type for properties of Doctrine entities.

The content of properties mapped with this type is serialized in JSON using the [Symfony Serializer](http://symfony.com/doc/current/components/serializer.html)
then, it is stored in a dynamic JSON column in the database.

When the object will be hydrated, the JSON content of this column is transformed back to its original values, thanks again
to the Symfony Serializer.
All PHP objects and structures will be preserved.

You can store any type of (serializable) PHP data structures in properties mapped using the `json_document` type.

Example:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping\{Entity, Column, Id, GeneratedValue};

// This is a typical Doctrine ORM entity.
#[Entity]
class Foo
{
  #[Column]
  #[Id]
  #[GeneratedValue]
  public int $id;

  #[Column]
  public string $name;

  // Can contain anything: array, objects, nested objects...
  #[Column(type: 'json_document', options: ['jsonb' => true])]
  public $misc;

  // Works with private and protected methods with getters and setters too.
}
```

```php
namespace App\Entity;

// This is NOT an entity! It's a POPO (Plain Old PHP Object). It can contain anything.
class Bar
{
    public string $title;
    public float $weight;
}
```

```php
namespace App\Entity;

// This is NOT an entity. It's another POPO and it can contain anything.
class Baz
{
    public string $name;
    public int $size;
}
```

Store a graph of random object in the JSON type of the database:

```php
// $entityManager = $managerRegistry->getManagerForClass(Foo::class);

$bar = new Bar();
$bar->title = 'Bar';
$bar->weight = 12.3;

$baz = new Baz();
$baz->name = 'Baz';
$baz->size = 7;

$foo = new Foo();
$foo->name = 'Foo';
$foo->misc = [$bar, $baz];

$entityManager->persist($foo);
$entityManager->flush();
```

Retrieve the object graph back:

```php
$foo = $entityManager->find(Foo::class, $foo->getId());
var_dump($foo->misc); // Same as what we set earlier
```

### Using type aliases

Using custom type aliases as `#type` rather than FQCNs has a couple of benefits:
- In case you move or rename your document classes, you can just update your type map without migrating database content
- For applications that might store millions of records with JSON documents, this can also save some storage space

You can introduce type aliases at any point in time. Already persisted JSON documents with class names will still get deserialized correctly.

#### Using Symfony

In order to use type aliases, add the bundle configuration, e.g. in `config/packages/doctrine_json_odm.yaml`:

```yaml
dunglas_doctrine_json_odm:
    type_map:
        foo: App\Something\Foo
        bar: App\SomethingElse\Bar
```

With this, `Foo` objects will be serialized as:

```json
{ "#type": "foo", "someProperty": "someValue" }
```

Another option is to use your own custom type mapper implementing `Dunglas\DoctrineJsonOdm\TypeMapperInterface`. For this, just override the service definition:

```yaml
services:
    dunglas_doctrine_json_odm.type_mapper: '@App\Something\MyFancyTypeMapper'
```

#### Without Symfony

When instantiating `Dunglas\DoctrineJsonOdm\Serializer`, you need to pass an extra argument that implements `Dunglas\DoctrineJsonOdm\TypeMapperInterface`.

For using the built-in type mapper:

```php
    // …
    use Dunglas\DoctrineJsonOdm\Serializer;
    use Dunglas\DoctrineJsonOdm\TypeMapper;
    use App\Something\Foo;
    use App\SomethingElse\Bar;
    
    // For using the built-in type mapper:
    $typeMapper = new TypeMapper([
        'foo' => Foo::class,
        'bar' => Bar::class,
    ]);
    
    // Or implement TypeMapperInterface with your own class:
    $typeMapper = new MyTypeMapper();

    // Then pass it into the Serializer constructor
    Type::getType('json_document')->setSerializer(
        new Serializer([new ArrayDenormalizer(), new ObjectNormalizer()], [new JsonEncoder()], $typeMapper)
    );
```


### Limitations when updating nested properties

Due to how Doctrine works, it will not detect changes to nested objects or properties.
The reason for this is that Doctrine compares objects by reference to optimize `UPDATE` queries.
If you experience problems where no `UPDATE` queries are executed, you might need to `clone` the object before you set it.
That way Doctrine will notice the change. See https://github.com/dunglas/doctrine-json-odm/issues/21 for more information.

## FAQ

**What DBMS are supported?**

PostgreSQL 9.4+ and MySQL 5.7+ are supported.

**Which versions of Doctrine are supported?**

Doctrine ORM 2.6+ and DBAL 2.6+ are supported.

**How to use [the JSONB type of PostgreSQL](http://www.postgresql.org/docs/current/static/datatype-json.html)?**

Then, you need to set an option in the column mapping:

```php
// ...

    #[Column(type: 'json_document', options: ['jsonb' => true])]
    public $foo;

// ...
```

**Does the ODM support nested objects and object graphs?**

Yes.

**Can I use the native [PostgreSQL](http://www.postgresql.org/docs/current/static/datatype-json.html) and [MySQL](https://dev.mysql.com/doc/refman/en/json.html) /JSON functions?**

Yes! You can execute complex queries using [native queries](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/native-sql.html). 

Alternatively, install [scienta/doctrine-json-functions](https://github.com/ScientaNL/DoctrineJsonFunctions) to be able to use run JSON functions in DQL and query builders.

**How to change the (de)serialization context**

You may need to change the (de)serialization context, for instance to avoid escaping slashes.

If you are using Symfony, modify your Kernel like this:

```php
<?php
// src/Kernel.php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        $type = Type::getType('json_document');
        $type->setSerializationContext([JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES]);
        $type->setDeserializationContext([/* ... */]);
    }
}
```

**How can I add additional normalizers?**

The Symfony Serializer is easily extensible. This bundle registers and uses a service with ID `dunglas_doctrine_json_odm.serializer` as the serializer for the JSON type. This means we can easily override it in our `services.yaml` to use additional normalizers.
As an example we use the Symfony `DateTimeNormalizer` service, so we do have support for any property that is an instance of `\DateTimeInterface`. Be aware that the order of the normalizers might be relevant depending on the normalizers you use.

```yaml
    # Add DateTime Normalizer to Dunglas' Doctrine JSON ODM Bundle
    dunglas_doctrine_json_odm.serializer:
        class: Dunglas\DoctrineJsonOdm\Serializer
        arguments:
          - ['@dunglas_doctrine_json_odm.normalizer.array', '@serializer.normalizer.datetime', '@dunglas_doctrine_json_odm.normalizer.object']
          - ['@serializer.encoder.json']
        public: true
```

As a side note: If you happen to use [Autowiring](https://symfony.com/doc/current/service_container/autowiring.html) in your `services.yaml` you might need to set `autowire: false` too. Same goes for `autoconfigure: false` in case you're using [Autoconfiguration](https://symfony.com/doc/current/service_container.html#the-autoconfigure-option).

**When the namespace of a used entity changes**

For classes without [type aliases](#using-type-aliases), because we store the `#type` along with the data in the database, you have to migrate the already existing data in your database to reflect the new namespace.

Example: If we have a project that we migrate from `AppBundle` to `App`, we have the namespace `AppBundle/Entity/Bar` in our database which has to become `App/Entity/Bar` instead.

When you use `MySQL`, you can use this query to migrate the data:
```sql
UPDATE Baz
SET misc = JSON_REPLACE(misc, '$."#type"', 'App\\\Entity\\\Bar')
WHERE 'AppBundle\\\Entity\\\Bar' = JSON_EXTRACT(misc, '$."#type"');
```

## Credits

This bundle is brought to you by [Kévin Dunglas](https://dunglas.fr) and [awesome contributors](https://github.com/dunglas/doctrine-json-odm/graphs/contributors).
Sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).

## Former Maintainers

[Yanick Witschi](https://github.com/Toflar) helped maintain this bundle, thanks!
