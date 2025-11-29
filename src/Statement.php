<?php

namespace Xwero\IdableQueriesCore;

use Xwero\IdableQueriesCore\Error;

interface Statement
{
    public string $queryString { get; }

    /**
     * Stores parameter(s) until the run method is executed
     */
    public function bindParameter(string|int $param, mixed $value): true|Error;

    /**
     * Executes the database query string with optional parameters.
     * And returns the Least amount data.
     */
    public function run(QueryReturnConfig $config): mixed;
}