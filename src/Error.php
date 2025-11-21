<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

readonly class Error
{
    public function __construct(public \Exception $exception)
    {}
}