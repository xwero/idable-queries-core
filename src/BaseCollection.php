<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

abstract class BaseCollection
{
    protected array $collection = [];

    public function __construct(array $items)
    {
        $this->collection = $items;
    }

    public function getAll(): array
    {
        return $this->collection;
    }

    public function isEmpty(): bool
    {
        return count($this->collection) == 0;
    }
}