<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Closure;

final readonly class Alias
{
    public function __construct(public string $alias, public Identifier $identifier, public Closure|null $valueFilter = null)
    {}
}