<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

final readonly class QueryParameterCollection
{
    public function __construct(
        public string $query,
        public PlaceholderIdentifierCollection $parameters,
    )
    {}
}