<?php

declare(strict_types=1);

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\IdableParameterCollection;
use function Xwero\IdableQueriesCore\addIdableParameters;

test('no replacement', function (string $query, IdableParameterCollection $parameters) {
    expect(addIdableParameters($query, $parameters, getDefaultNamespace()))->toBe($query);
})->with([
    'bad regex' => [
        'SELECT * FROM users WHERE name=Users:Name',
        IdableParameterCollection::createWithIdableParameter(Users::Name, 'me'),
    ],
    'bad identifier' => [
        'SELECT * FROM users WHERE name=:Users:Email',
        IdableParameterCollection::createWithIdableParameter(Users::Name, 'me')
    ],
]);

test('replacements', function (string $query, IdableParameterCollection $parameters, string $result) {
    expect(addIdableParameters($query, $parameters, getDefaultNamespace()))->toBe($result);
})->with([
    'user name' => [
        'SELECT * FROM users WHERE name=:Users:Name',
        new IdableParameterCollection()->add(Users::Name, 'me'),
        "SELECT * FROM users WHERE name=me",
    ]
]);