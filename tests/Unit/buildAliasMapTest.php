<?php

declare(strict_types=1);

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\AliasCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\Map;
use function Xwero\IdableQueriesCore\buildAliasMap;

test('returns Error', function (array|Error $data, AliasCollection $collection, Closure|null $valueValidator) {
    expect(buildAliasMap($data, $collection))->toBeInstanceOf(Error::class);
})->with([
    'Error data' => [
        new Error(new Exception('test')),
        new AliasCollection(),
        null,
    ],
    'data keys integer' => [
        ['me'],
        new AliasCollection(),
        null,
    ],
    'no aliases' => [
        ['name' => 'me'],
        new AliasCollection(),
        null,
    ],
    'no return type on valueValidator' => [
        ['name' => 'me'],
        new AliasCollection(),
        fn($i) => is_string($i),
    ],
    'no bool return type on valueValidator' => [
        ['name' => 'me'],
        new AliasCollection(),
        fn($i): string => (string) $i,
    ],
]);

test('partial mapping', function (array $data, AliasCollection $aliases) {
    $map = buildAliasMap($data, $aliases);

    expect($map)->toBeInstanceOf(Map::class)
        ->and($map)->toHaveCount(1)
        ->and($map[Users::Name])->toBe('me');
})->with([
    'key not in AliasCollection' => [
        [
            'name' => 'me',
            'test' => 'not in map',
        ],
        AliasCollection::createWithItem('name', Users::Name),
        null,
    ],
    'values filtered by closure' => [
        [
            'name' => 'me',
            'email' => 1,
        ],
        AliasCollection::createWithItem('name', Users::Name)
            ->add( 'email', Users::Email, fn($i): bool => is_string($i)),
    ]
]);

test('full mapping', function () {
    $data = [
        'name' => 'me',
        'email' => 'email@test',
    ];
    $aliases = new AliasCollection()->add('name', Users::Name)->add( 'email', Users::Email);
    $map = buildAliasMap($data, $aliases);

    expect($map)->toBeInstanceOf(Map::class)
        ->and($map)->toHaveCount(2)
        ->and($map[Users::Name])->toBe('me')
        ->and($map[Users::Email])->toBe('email@test');
});