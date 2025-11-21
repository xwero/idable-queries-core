<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Closure;
use Xwero\IdableQueriesCore\BaseCollection;

class Chain extends BaseCollection
{
    public function __construct(Closure ...$functions)
    {
        parent::__construct($functions);
    }
}