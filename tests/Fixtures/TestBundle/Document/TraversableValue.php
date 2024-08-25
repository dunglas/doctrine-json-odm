<?php

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class TraversableValue implements ArrayAccess, IteratorAggregate
{
    private array $array;

    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->array[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->array[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->array);
    }
}
