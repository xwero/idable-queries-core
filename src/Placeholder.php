<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Closure;
use InvalidArgumentException;
use ReflectionClass;

final class Placeholder
{
    public function __construct(
        public readonly string     $placeholder,
        public readonly Identifier $identifier,
        public mixed               $value = null,
        private readonly string    $prefix = '',
        private readonly string    $suffix = '',
    )
    {}

    public function getFullPlaceholder(Closure|null $alterPlaceholder = null): string
    {
        $out = '';

        if(strlen($this->prefix)) {
            $out .= $this->prefix;
        }

        $out .= $alterPlaceholder !== null ? $alterPlaceholder($this->placeholder) : $this->placeholder;

        if(strlen($this->suffix)) {
            $out .= $this->suffix;
        }

        return $out;
    }

    public function getCustomValue(mixed $value): mixed
    {
        $reflection = new ReflectionClass($this->identifier);

        $result = $reflection->getAttributes(CustomParameterIdentifier::class);

        if(count($result) == 0) {
            return $value;
        }

        $transformer = $result[0]->newInstance()->placeholderTransformer;

        if( ! function_exists($transformer)) {
            $id = $reflection->getName();
            return new Error(new InvalidArgumentException("$transformer added to $id does not exist."));
        }

        $value = $transformer($this, $value);

        if($value instanceof Error) {
            return $value;
        }

        return $value;
    }
}