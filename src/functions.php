<?php

declare(strict_types=1);

namespace Xwero\IdableQueriesCore;

use BackedEnum;
use Closure;
use Exception;
use InvalidArgumentException;

/*
 * This is the more dangerous parameter function.
 * Use only if the values are validated before adding them to the query.
 */
function addIdableParameters(
    string                       $query,
    IdableParameterCollection    $parameters,
    BaseNamespaceCollection|null $namespaces = null,
): string|Error
{
    $placeholders = queryToPlaceholderIdentifierCollection($query, getParameterRegex(), $namespaces);

    if ($placeholders instanceof Error) {
        return $placeholders;
    }

    if ($placeholders->isEmpty()) {
        return $query;
    }

    $placeholders = $placeholders->getAll();
    $search = [];
    $replacements = [];

    foreach ($placeholders as $item) {
        if ($value = $parameters->findValueByIdentifierAndPlaceholder($item->identifier, $item->placeholder)) {
            $search[] = $item->placeholder;
            $replacements[] = $value;
        }
    }

    return str_replace($search, $replacements, $query);
}

function buildAliasMap(array|Error $data, AliasCollection $aliases): Map|Error
{
    if ($data instanceof Error) {
        return $data;
    }

    if (array_all(array_keys($data), fn($i) => is_string($i)) == false) {
        return new Error(new InvalidArgumentException("The data keys for the map need to be a strings."));
    }

    if($aliases->isEmpty()) {
        return new Error(new InvalidArgumentException("The alias collection must have items."));
    }

    $map = new Map();

    foreach($data as $possibleAlias => $value) {
        if($identifier = $aliases->getIdentifier($possibleAlias)) {
            $map[$identifier] = $value;
        }
    }

    return $map;
}

function buildAliasesMapCollection(array|Error $data, AliasCollection $aliases): MapCollection|Error
{
    if ($data instanceof Error) {
        return $data;
    }

    if (array_all(array_keys($data), fn($item) => is_int($item)) == false) {
        return new Error(new InvalidArgumentException("The data keys on the first level need to be integers."));
    }

    if(array_all($data, fn($item) => is_array($item)) == false) {
        return new Error(new InvalidArgumentException("The data values need to be arrays."));
    }

    if($aliases->isEmpty()) {
        return new Error(new InvalidArgumentException("The alias collection must have items."));
    }

    $collection = new MapCollection();

    foreach($data as $mapable) {
        $map = buildAliasMap($mapable, $aliases);

        if($map instanceof Error) {
            return $map;
        }

        $collection->add($map);
    }

    return $collection;
}

function buildLevelMap(
    array                           $data,
    PlaceHolderIdentifierCollection $placeholders,
    AliasCollection|null            $aliases = null,
): Map|Error
{
    if (array_all(array_keys($data), fn($i) => is_string($i)) == false) {
        return new Error(new InvalidArgumentException("The data keys for the map need to be a strings."));
    }

    $placeholders = $placeholders->getAll();
    $map = new Map();

    foreach ($placeholders as $item) {
        if ($item->identifier instanceof Identifier) {
            $queryReplacement = queryStringFromIdentifier($item->identifier);

            if (array_key_exists($queryReplacement, $data)) {
                $map[$item->identifier] = $data[$queryReplacement];
            }
        }
    }

    if ($aliases instanceof AliasCollection) {
        foreach ($data as $key => $value) {
            if ($identifier = $aliases->getIdentifier($key)) {
                $map[$identifier] = $value;
            }
        }
    }

    return $map;
}

function collectIdableParameters(
    string                       $query,
    IdableParameterCollection    $parameters,
    BaseNamespaceCollection|null $namespaces = null,
): PlaceholderIdentifierCollection|Error
{
    $placeholders = queryToPlaceholderIdentifierCollection($query, getParameterRegex(), $namespaces);

    if ($placeholders instanceof Error) {
        return $placeholders;
    }

    $placeholderReplacements = new PlaceholderIdentifierCollection();

    if ($placeholders->isEmpty()) {
        return $placeholderReplacements;
    }

    $placeholders = $placeholders->getAll();

    foreach ($placeholders as $item) {
        if ($value = $parameters->findValueByIdentifierAndPlaceholder($item->identifier, $item->placeholder)) {
            $phi = new PlaceholderIdentifier($item->placeholder, $item->identifier);
            $value = $phi->getCustomValue($value);

            if($value instanceof Error) {
                return $value;
            }

            $phi->value = $value;

            $placeholderReplacements->add($phi);
        }
    }

    return $placeholderReplacements;
}

function createMapFromFirstLevelResults(
    array|Error                  $data,
    string                       $query,
    AliasCollection|null         $aliases = null,
    BaseNamespaceCollection|null $namespaces = null,
): Map|Error
{
    if ($data instanceof Error) {
        return $data;
    }

    $placeholders = queryToPlaceholderIdentifierCollection($query, getIdentifierRegex(), $namespaces);

    if ($placeholders instanceof Error) {
        return $placeholders;
    }

    return $placeholders->isEmpty() ? new Map() : buildLevelMap($data, $placeholders, $aliases);
}

function createMapFromSecondLevelResults(
    array|Error                  $data,
    string                       $query,
    AliasCollection|null         $aliases = null,
    BaseNamespaceCollection|null $namespaces = null,
): MapCollection|Error
{
    if ($data instanceof Error) {
        return $data;
    }

    if (array_all(array_keys($data), fn($item) => is_int($item)) == false) {
        return new Error(new InvalidArgumentException("The data keys on the first level need to be integers."));
    }

    if(array_all($data, fn($item) => is_array($item)) == false) {
        return new Error(new InvalidArgumentException("The data values need to be arrays."));
    }

    $placeholders = queryToPlaceholderIdentifierCollection($query, getIdentifierRegex(), $namespaces);

    if ($placeholders instanceof Error) {
        return $placeholders;
    }

    $collection = new MapCollection();

    if ($placeholders->isEmpty()) {
        return $collection;
    }

    foreach ($data as $item) {
        $map = buildLevelMap($item, $placeholders, $aliases);

        if ($map instanceof Error) {
            return $map;
        }

        if ($map->count() > 0) {
            $collection->add($map);
        }
    }

    return $collection;
}

function isIdableQuery(string $query): bool
{
    preg_match_all(getIdentifierRegex(), $query, $DBMatches);
    preg_match_all(getParameterRegex(), $query, $ParamMatches);

    return (isset($DBMatches[0]) && count($DBMatches[0]) > 0) || (isset($ParamMatches[0]) && count($ParamMatches[0]) > 0);
}

function getIdentifierFromStrings(string $class, string $case): Identifier|null
{
    if (!class_exists($class)) {
        return null;
    }

    if (!method_exists($class, 'cases')) {
        return null;
    }

    $cases = $class::cases();
    $case = ucfirst($case);

    if (!$cases[0] instanceof Identifier) {
        return null;
    }
    // When the case is used multiple times it can be suffixed with a number.
    $case = preg_replace('/(\d+)$/', '', $case);

    foreach ($cases as $c) {
        if ($c->name == $case) {
            return $c;
        }
    }

    return null;
}

function getIdentifierRegex(): string
{
    $regex = getenv('IDENTIFIER_REGEX');
    return is_string($regex) ? $regex : "(~[A-Za-z1-9\\\]+:[A-Za-z1-9]+)";
}

function getParameterRegex(): string
{
    $regex = getenv('PARAMETER_REGEX');
    return is_string($regex) ? $regex : "(:[A-Za-z1-9\\\]+:[A-Za-z1-9]+)";
}

function queryStringFromIdentifier(Identifier $identifier): string
{
    return $identifier instanceof BackedEnum ? $identifier->value : strtolower($identifier->name);
}

function queryToPlaceholderIdentifierCollection(
    string|Error                 $query,
    string                       $regex,
    BaseNamespaceCollection|null $namespaces = null,
): PlaceHolderIdentifierCollection|Error
{
    if($query instanceof Error) {
        return $query;
    }

    preg_match_all($regex, $query, $matches);

    $collection = new PlaceholderIdentifierCollection();
    $hasMatches = isset($matches[0]) && count($matches[0]) > 0;

    if ( ! $hasMatches) {
        return $collection;
    }

    $set = array_unique($matches[0]);
    // Order from the largest strings to the smallest strings to prevent similar named string errors during replacement.
    usort($set, fn($a, $b) => strlen($b) <=> strlen($a));

    foreach ($set as $item) {
        try {
            $pair = explode(':', substr($item, 1), 2);

            if (class_exists($pair[0])) {
                $replacement = getIdentifierFromStrings($pair[0], $pair[1]);
                if ($replacement instanceof Identifier) {
                    $collection->add(new PlaceholderIdentifier($item, $replacement));
                    continue;
                }
            }

            if ($namespaces instanceof BaseNamespaceCollection) {
                foreach ($namespaces->getAll() as $baseNamespace) {
                    $possibleClass = $baseNamespace . '\\' . $pair[0];
                    if (class_exists($possibleClass)) {
                        $replacement = getIdentifierFromStrings($possibleClass, $pair[1]);
                        if ($replacement instanceof Identifier) {
                            $collection->add(new PlaceholderIdentifier($item, $replacement));
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return new Error($e);
        }
    }

    if($collection->isEmpty()) {
        return new Error(new InvalidArgumentException("The collection is empty while placeholders are found in the query. Bad namespaces can be the cause."));
    }

    return $collection;
}

function replaceIdentifiersInQuery(
    string                       $query,
    BaseNamespaceCollection|null $namespaces = null,
): string|Error
{
    $placeholderIdentifierCollection = queryToPlaceholderIdentifierCollection($query, getIdentifierRegex(), $namespaces);

    if ($placeholderIdentifierCollection instanceof Error) {
        return $placeholderIdentifierCollection;
    }

    if($placeholderIdentifierCollection->isEmpty()) {
        return $query;
    }

    $placeholderIdentifierCollection = $placeholderIdentifierCollection->getAll();
    $placeholders = [];
    $replacements = [];

    foreach ($placeholderIdentifierCollection as $item) {
        if ($item->identifier instanceof Identifier) {
            $replacements[] = queryStringFromIdentifier($item->identifier);
            $placeholders[] = $item->placeholder;
        }
    }

    return str_replace($placeholders, $replacements, $query);
}

function replaceParametersInQuery(
    string                       $query,
    IdableParameterCollection    $parameters,
    BaseNamespaceCollection|null $namespaces = null,
    Closure|null                 $placeholderTransformer = null
): QueryParameterCollection|Error
{
    $placeholderCollection = collectIdableParameters($query, $parameters, $namespaces);

    if ($placeholderCollection instanceof Error) {
        return $placeholderCollection;
    }

    $replacements = $placeholderCollection->getPlaceholderReplacements($placeholderTransformer);
    $query = str_replace(array_keys($replacements), $replacements, $query);

    return new QueryParameterCollection($query, $placeholderCollection);
}

// Function chaining for PHP versions under 8.5
function runChain(Chain $chain) : mixed
{
    if($chain->isEmpty()) {
        return new Error(new InvalidArgumentException('Chain have at least on function to get a result.'));
    }

    $functions = $chain->getAll();
    $first = $functions[0];
    $result = $first();
    unset($functions[0]);

    foreach ($functions as $function) {
        $result = $function($result);
    }

    return $result;
}





