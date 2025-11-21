<?php

declare(strict_types=1);

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\Identifier;
use Xwero\IdableQueriesCore\IdableParameterCollection;

test('find without placeholder', function (Identifier $key, mixed $value) {
    $collection = new IdableParameterCollection()->add($key, $value);

    expect($collection->findValueByIdentifierAndPlaceholder($key))->toBe($value);
})->with([
    'string' => [Users::Name, 'me'],
    'int' => [Users::Id, 1],
    'float' => [Users::Id, 1.1],
]);

test('find with placeholder', function (Identifier $key, mixed $value, int $numberSuffix, string $placeholder) {
    $collection = new IdableParameterCollection()->add($key, $value, $numberSuffix);

    expect($collection->findValueByIdentifierAndPlaceholder($key, $placeholder))->toBe($value);
})->with([
    'string' => [
        Users::Name,
        'me',
        1,
        ':Users:Name1',
    ],
]);