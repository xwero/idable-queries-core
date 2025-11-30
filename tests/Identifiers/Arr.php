<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\CustomParameterIdentifier;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\Identifier;
use Xwero\IdableQueriesCore\Placeholder;
use Xwero\IdableQueriesCore\PlaceholderCollection;

function arrayTransformer(Placeholder $phi, array $value, string $placeholderSeparator = '_'): Error|PlaceholderCollection
{
    if(count($value) > 2) {
        return new Error(new \InvalidArgumentException('The value array must have at least 2 elements.'));
    }

    $collection = new PlaceholderCollection();
    $count = count($value) - 1;
    $counter = 0;

    foreach ($value as $v) {
        if ($counter == 0) {
            $collection->addPlaceholderIdentifier(new Placeholder(
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
            $collection->addPlaceholderIdentifier(new Placeholder(
                    $phi->placeholder . $placeholderSeparator . $counter,
                    $phi->identifier,
                    value: $v,
                    suffix: ')'
                )
            );
            $counter++;
            continue;
        }

        $collection->addPlaceholderIdentifier(new Placeholder(
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
