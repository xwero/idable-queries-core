<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

class IdableParameterCollection extends BaseCollection
{
    public function __construct(IdableParameter ...$items)
    {
        parent::__construct($items);
    }

    public static function createWithItem(Identifier $identifier, array|int|float|string $value, int $numberSuffix = 0): self
    {
        return new self()->add($identifier, $value, $numberSuffix);
    }

    public function add(Identifier $identifier, array|int|float|string $value, int $numberSuffix = 0): self
    {
        $this->collection[] = new IdableParameter($identifier, $value, $numberSuffix);

        return $this;
    }

    public function addIdableParameter(IdableParameter $idableParameter): self
    {
        $this->collection[] = $idableParameter;

        return $this;
    }

    public function findValueByIdentifierAndPlaceholder(Identifier $identifier, string $placeholder = ''): array|int|float|string|false
    {
        $suffix = 0;

        if (preg_match('/(\d+)$/', $placeholder, $m)) {
            $suffix = (int) $m[1];   // cast to int (use (float) if you need decimals)
        }

        foreach ($this->collection as $item) {
            if($item->identifier === $identifier && $item->numberSuffix === $suffix) {
                return $item->value;
            }
        }

        return false;
    }
}