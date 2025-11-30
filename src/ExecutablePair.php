<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

 final readonly class ExecutablePair
{
    public function __construct(public Statement $statement, public QueryReturnConfig $returnConfig)
    {}
}