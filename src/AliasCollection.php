<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Closure;
use Exception;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionNamedType;

class AliasCollection extends BaseCollection
{
    public function __construct(Alias ...$items)
    {
        parent::__construct($this->getAliaskeyedArray($items));
    }

    public static function createWithItem(string $alias, Identifier $identifier, Closure|null $valueFilter = null) : Error|self
    {
        return new self()->add($alias, $identifier, $valueFilter);
    }

    public function add(string $alias, Identifier $identifier, Closure|null $valueFilter = null): Error|self
    {
        if($valueFilter instanceof Closure) {
            try {
                $reflectionReturnType = new ReflectionFunction($valueFilter)->getReturnType();
            } catch (Exception $exception) {
                return new Error($exception);
            }

            if ($reflectionReturnType === null) {
                return new Error(new InvalidArgumentException("The value validator must have a return type."));
            }

            if ($reflectionReturnType instanceof ReflectionNamedType && $reflectionReturnType->getName() != 'bool') {
                return new Error(new InvalidArgumentException("The value validator must have a bool return type."));
            }
        }

        $this->collection[$alias] = new Alias($alias, $identifier, $valueFilter);

        return $this;
    }

    public function addAlias(Alias $aias): self
    {
        $this->collection[] = $aias;

        return $this;
    }

    public function getAlias(string $key): Alias|null
    {
        if(isset($this->collection[$key])) {
            return $this->collection[$key];
        }

        return null;
    }

    private function getAliasKeyedArray(array $aliases): array
    {
        if(count($aliases) === 0) {
            return $aliases;
        }

        $temp = [];

        foreach ($aliases as $item) {
            $temp[$item->alias] = $item;
        }

        return $temp;
    }
}