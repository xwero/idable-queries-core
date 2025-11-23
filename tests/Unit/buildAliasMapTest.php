<?php

declare(strict_types=1);

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\AliasCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\Map;
use function Xwero\IdableQueriesCore\buildAliasMap;

test('returns Error', function (array|Error $data, AliasCollection $collection) {
    expect(buildAliasMap($data, $collection))->toBeInstanceOf(Error::class);
})->with([
    'Error data' => [
        new Error(new Exception('test')),
        new AliasCollection(),
    ],
    'data keys integer' => [
        ['me'],
        new AliasCollection(),
    ],
    'no aliases' => [
        ['name' => 'me'],
        new AliasCollection(),
    ],
]);

test('partial mapping', function () {
    $data = [
        'name' => 'me',
        'test' => 'not in map',
    ];
    $aliases = new AliasCollection('name', Users::Name);
    $map = buildAliasMap($data, $aliases);

    expect($map)->toBeInstanceOf(Map::class)
        ->and($map)->toHaveCount(1)
        ->and($map[Users::Name])->toBe('me');
});

test('full mapping', function () {
    $data = [
        'name' => 'me',
        'email' => 'email@test',
    ];
    $aliases = new AliasCollection('name', Users::Name, 'email', Users::Email);
    $map = buildAliasMap($data, $aliases);

    expect($map)->toBeInstanceOf(Map::class)
        ->and($map)->toHaveCount(2)
        ->and($map[Users::Name])->toBe('me')
        ->and($map[Users::Email])->toBe('email@test');
});