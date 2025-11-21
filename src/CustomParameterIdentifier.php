<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class CustomParameterIdentifier
{
    public function __construct(public string $placeholderTransformer)
    {
    }
}