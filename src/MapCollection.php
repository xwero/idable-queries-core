<?php

namespace Xwero\IdableQueriesCore;

class MapCollection extends BaseCollection
{
    public function __construct(Map ...$items)
    {
        parent::__construct($items);
    }

    public function add(Map $item): self
    {
        $this->collection[] = $item;

        return $this;
    }
}