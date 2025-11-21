<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Xwero\IdableQueriesCore\TypeCollection;

class AliasCollection extends TypeCollection
{
    public function __construct(Identifier|string ...$pairs)
    {
        $filteredKeys = array_filter($pairs, fn($item) => is_string($item));
        $filteredValues = array_filter($pairs, fn($item) => $item instanceof Identifier);

        $this->fillArrays($filteredKeys, $filteredValues);
    }

    public function getIdentifier(string $key): Identifier|null
    {
        $valueKey = array_search($key, $this->keys);

        return is_int($valueKey) ? $this->values[$valueKey] : null;
    }
}