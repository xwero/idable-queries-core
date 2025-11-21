<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Closure;

class PlaceholderIdentifierCollection extends BaseCollection
{
    public function __construct(PlaceholderIdentifier ...$items)
    {
        parent::__construct($items);
    }

    public function add(PlaceholderIdentifier $item) : self {
        $this->collection[] = $item;

        return $this;
    }

    public function getPlaceholderReplacements(
        Closure|null $placeholderTransformer = null,
        PlaceholderIdentifierCollection|null $collection = null,
        string|null $placeholder = null,
    ): array
    {
        $out = [];

        if($placeholderTransformer instanceof Closure && is_null($collection) &&
            array_all($this->collection,
                fn($item) => $item->value !== null && ! $item->value instanceof PlaceholderIdentifierCollection
                    && ! $item->value instanceof PlaceholderIdentifier)
        ) {
            foreach ($this->collection as $item) {
                $out[$item->placeholder] = $placeholderTransformer($item->placeholder);
            }

            return $out;
        }

        $phis = $collection ?  $collection->getAll() : $this->collection;

        $deeperLevelSuspects = array_filter($phis, fn($item) => $item->value instanceof PlaceholderIdentifierCollection || $item->value instanceof PlaceholderIdentifier);

        if(count($deeperLevelSuspects) > 0) {
            foreach ($deeperLevelSuspects as $deeperLevelSuspect) {
                if($deeperLevelSuspect->value instanceof PlaceholderIdentifierCollection) {
                    $placeholders = $deeperLevelSuspect->value->getPlaceholdersAsText($placeholderTransformer);
                    if(strlen($placeholders) > 0) {
                        $out[$deeperLevelSuspect->placeholder] = $placeholders;
                        continue;
                    }
                }

                if($deeperLevelSuspect->value instanceof PlaceholderIdentifier) {
                    if($deeperLevelSuspect->value->value instanceof PlaceholderIdentifierCollection) {
                        $out = array_merge($out, $this->getPlaceholderReplacements($placeholderTransformer, $deeperLevelSuspect->value->value, $deeperLevelSuspect->placeholder));
                    }
                }
            }
        }

        if($collection instanceof PlaceholderIdentifierCollection && count($deeperLevelSuspects) == 0) {
            $out[$placeholder] = $collection->getPlaceholdersAsText($placeholderTransformer);
        }

        return $out;
    }

    public function getPlaceholdersAsText(Closure|null $alterPlaceholder = null): string
    {
        $out = '';

        if(array_all($this->collection,
            fn($item) => $item->value instanceof PlaceholderIdentifierCollection || $item->value instanceof PlaceholderIdentifier))
        {
            return $out;
        }

        foreach ($this->collection as $placeholder) {
            $out .= $placeholder->getFullPlaceholder($alterPlaceholder);
        }

        return $out;
    }

    public function getPlaceholderValuePairs(
        Closure|null $placeholderTransformer = null,
        PlaceholderIdentifierCollection|null $collection = null,
    ): array
    {
        $out = [];
        $phrs = $collection ?  $collection->getAll() : $this->collection;

        foreach ($phrs as $phr) {
            $value = $phr->value;

            if($value instanceof PlaceholderIdentifierCollection) {
                $out = array_merge($out, $this->getPlaceholderValuePairs(collection: $value));
                continue;
            }

            if($value instanceof PlaceholderIdentifier) {
                if($value->value instanceof PlaceholderIdentifierCollection) {
                    $out = array_merge($out, $this->getPlaceholderValuePairs(collection: $value->value));
                    continue;
                }

                if($value->value instanceof PlaceholderIdentifier) {
                    $out = $this->placeholderIdentifierValueHandler($value->value, $out);
                    continue;
                }
            }

            if($value !== null) {
                $out[$phr->placeholder] = $value;
            }
        }

        if(count($out) > 0 && $placeholderTransformer instanceof Closure) {
            $newKeys = array_map(fn($item) => $placeholderTransformer($item), array_keys($out));
            $out = array_combine($newKeys, $out);
        }

        return $out;
    }

    private function placeholderIdentifierValueHandler(PlaceholderIdentifier $phi, array $output): mixed
    {
        if($phi->value instanceof PlaceholderIdentifierCollection) {
            $output = array_merge($output, $this->getPlaceholderValuePairs(collection: $phi->value));
        }

        if($phi->value instanceof PlaceholderIdentifier) {
            $output = $this->placeholderIdentifierValueHandler($phi->value, $output);
        }

        return $output;
    }
}