<?php

namespace Xwero\IdableQueriesCore;

interface Statement
{
    public string $queryString { get; }

    public function bindValue(string|int $param, mixed $value, int $type): bool;

    public function execute(array|null $params = null): bool;
}