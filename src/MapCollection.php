<?php

namespace Xwero\IdableQueriesCore;

class MapCollection extends BaseCollection
{
    public function __construct(Map ...$items)
    {
        parent::__construct($items);
    }

    public function addMap(Map $item): self
    {
        $this->collection[] = $item;

        return $this;
    }
}