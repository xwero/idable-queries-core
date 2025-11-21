<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

final readonly class IdableParameter
{
    public function __construct(public Identifier $identifier, public array|int|float|string $value, public int $numberSuffix = 0)
    {}
}