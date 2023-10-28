<?php

namespace Dunglas\DoctrineJsonOdm\Tests\Fixtures\TestBundle\Document;

class Vector implements \Iterator
{
    private int $position;

    private array $array;

    public function __construct(array $array = null)
    {
        $this->array = $array ?? [];
        $this->position = 0;
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function current(): mixed
    {
        return $this->array[$this->key()];
    }
	
	public function key(): mixed
    {
        return $this->position;
    }

	public function next(): void
    {
        ++$this->position;
    }

	public function rewind(): void
    {
        $this->position = 0;
    }

	public function valid(): bool
    {
        return isset($this->array[$this->key()]);
    }
}
