<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

readonly class JSONError extends Error
{
    public function __construct(string $message)
    {
        parent::__construct(new JSONException($message));
    }
}