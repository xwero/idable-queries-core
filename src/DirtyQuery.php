<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

final readonly class DirtyQuery
{
    public function __construct(
        public string                $query,
        public PlaceholderCollection $parameters,
    )
    {}
}