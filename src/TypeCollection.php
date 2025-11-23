<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

abstract class TypeCollection
{
    protected array $keys = [];
    protected array $values = [];

    protected function fillArrays(array $keys, array $values): void
    {
        // Having more values than keys means storing too much information.
        if (count($keys) < count($values)) {
            $values = array_slice($values, 0, count($keys));
        }

        $this->keys = array_values($keys);
        $this->values = array_values($values);
    }

    public function isEmpty(): bool
    {
        return count($this->keys) == 0;
    }
}