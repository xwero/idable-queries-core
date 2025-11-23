<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\CustomParameterIdentifier;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\Identifier;
use Xwero\IdableQueriesCore\PlaceholderIdentifier;
use Xwero\IdableQueriesCore\PlaceholderIdentifierCollection;

function arrayTransformer(PlaceholderIdentifier $phi, array $value, string $placeholderSeparator = '_'): Error|PlaceholderIdentifierCollection
{
    if(count($value) > 2) {
        return new Error(new \InvalidArgumentException('The value array must have at least 2 elements.'));
    }

    $collection = new PlaceholderIdentifierCollection();
    $count = count($value) - 1;
    $counter = 0;

    foreach ($value as $v) {
        if ($counter == 0) {
            $collection->addPlaceholderIdentifier(new PlaceholderIdentifier(
                    $phi->placeholder . $placeholderSeparator . $counter,
                    $phi->identifier,
                    value: $v,
                    prefix: '(',
                    suffix: ','
                )
            );
            $counter++;
            continue;
        }

        if ($counter == $count) {
            $collection->addPlaceholderIdentifier(new PlaceholderIdentifier(
                    $phi->placeholder . $placeholderSeparator . $counter,
                    $phi->identifier,
                    value: $v,
                    suffix: ')'
                )
            );
            $counter++;
            continue;
        }

        $collection->addPlaceholderIdentifier(new PlaceholderIdentifier(
                $phi->placeholder . $placeholderSeparator . $counter,
                $phi->identifier,
                value: $v,
                suffix: ','
            )
        );
        $counter++;
    }

    return $collection;
}

#[CustomParameterIdentifier('Test\Identifiers\arrayTransformer')]
enum Arr implements Identifier
{
    case Test;
}
