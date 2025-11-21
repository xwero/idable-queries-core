<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

class BaseNamespaceCollection extends BaseCollection
{

    public function __construct(string ...$namespaces)
    {
        parent::__construct($namespaces);
    }
}