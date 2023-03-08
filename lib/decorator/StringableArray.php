<?php

namespace RDN\Error\Decorator;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

class StringableArray implements IteratorAggregate
{
    protected array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function __toString(): string
    {
        return print_r($this->data, true);
    }
}
