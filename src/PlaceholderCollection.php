<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use Closure;
use Exception;

class PlaceholderCollection extends BaseCollection
{
    public function __construct(Placeholder ...$items)
    {
        parent::__construct($items);
    }

    public static function createWithItem(
        string     $placeholder,
        Identifier $identifier,
        mixed      $value = null,
        string     $prefix = '',
        string     $suffix = '',
    ): self
    {
        return new self()->add($placeholder, $identifier, $value, $prefix, $suffix);
    }
    public function add(
        string     $placeholder,
        Identifier $identifier,
        mixed      $value = null,
        string     $prefix = '',
        string     $suffix = '',
    ) : self {
        $this->collection[] = new Placeholder($placeholder, $identifier, $value, $prefix, $suffix);

        return $this;
    }

    public function addPlaceholderIdentifier(Placeholder $phi): self
    {
        $this->collection[] = $phi;

        return $this;
    }

    public function getPlaceholderReplacements(
        Closure|null               $placeholderTransformer = null,
        PlaceholderCollection|null $collection = null,
        string|null                $placeholder = null,
    ): array
    {
        $out = [];

        if($placeholderTransformer instanceof Closure && is_null($collection) &&
            array_all($this->collection,
                fn($item) => $item->value !== null && ! $item->value instanceof PlaceholderCollection
                    && ! $item->value instanceof Placeholder)
        ) {
            foreach ($this->collection as $item) {
                $out[$item->placeholder] = $placeholderTransformer($item->placeholder);
            }

            return $out;
        }

        $phis = $collection ?  $collection->getAll() : $this->collection;

        $deeperLevelSuspects = array_filter($phis, fn($item) => $item->value instanceof PlaceholderCollection || $item->value instanceof Placeholder);

        if(count($deeperLevelSuspects) > 0) {
            foreach ($deeperLevelSuspects as $deeperLevelSuspect) {
                if($deeperLevelSuspect->value instanceof PlaceholderCollection) {
                    $placeholders = $deeperLevelSuspect->value->getPlaceholdersAsText($placeholderTransformer);
                    if(strlen($placeholders) > 0) {
                        $out[$deeperLevelSuspect->placeholder] = $placeholders;
                        continue;
                    }
                }

                if($deeperLevelSuspect->value instanceof Placeholder) {
                    if($deeperLevelSuspect->value->value instanceof PlaceholderCollection) {
                        $out = array_merge($out, $this->getPlaceholderReplacements($placeholderTransformer, $deeperLevelSuspect->value->value, $deeperLevelSuspect->placeholder));
                    }
                }
            }
        }

        if($collection instanceof PlaceholderCollection && count($deeperLevelSuspects) == 0) {
            $out[$placeholder] = $collection->getPlaceholdersAsText($placeholderTransformer);
        }

        return $out;
    }

    public function getPlaceholdersAsText(Closure|null $alterPlaceholder = null): string
    {
        $out = '';

        if(array_all($this->collection,
            fn($item) => $item->value instanceof PlaceholderCollection || $item->value instanceof Placeholder))
        {
            return $out;
        }

        foreach ($this->collection as $placeholder) {
            $out .= $placeholder->getFullPlaceholder($alterPlaceholder);
        }

        return $out;
    }

    public function getPlaceholderValuePairs(
        Closure|null               $placeholderTransformer = null,
        PlaceholderCollection|null $collection = null,
    ): array
    {
        $out = [];
        $phrs = $collection ?  $collection->getAll() : $this->collection;

        foreach ($phrs as $phr) {
            $value = $phr->value;

            if($value instanceof PlaceholderCollection) {
                $out = array_merge($out, $this->getPlaceholderValuePairs(collection: $value));
                continue;
            }

            if($value instanceof Placeholder) {
                if($value->value instanceof PlaceholderCollection) {
                    $out = array_merge($out, $this->getPlaceholderValuePairs(collection: $value->value));
                    continue;
                }

                if($value->value instanceof Placeholder) {
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

    private function placeholderIdentifierValueHandler(Placeholder $phi, array $output): mixed
    {
        if($phi->value instanceof PlaceholderCollection) {
            $output = array_merge($output, $this->getPlaceholderValuePairs(collection: $phi->value));
        }

        if($phi->value instanceof Placeholder) {
            $output = $this->placeholderIdentifierValueHandler($phi->value, $output);
        }

        return $output;
    }
}