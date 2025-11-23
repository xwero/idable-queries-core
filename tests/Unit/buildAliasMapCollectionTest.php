<?php

declare(strict_types=1);

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\AliasCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\MapCollection;
use function Xwero\IdableQueriesCore\buildAliasesMapCollection;

test('returns Error', function (array|Error $data, AliasCollection $aliases) {
    expect(buildAliasesMapCollection($data, $aliases))->toBeInstanceOf(Error::class);
})->with([
    'Error data' => [
        new Error(new Exception('test')),
        new AliasCollection(),
    ],
    'data keys string' => [
        ['test' => 'test'],
        new AliasCollection(),
    ],
    'data values not an array' => [
        ['test'],
        new AliasCollection(),
    ],
    'no aliases' => [
        [['test']],
        new AliasCollection(),
    ],
    'no string keys on second level' => [
        [['test']],
        new AliasCollection('name', Users::Name),
    ]
]);

test('happy path', function () {
   $data = [['name' => 'me', 'email' => 'email@test']];
   $aliases = new AliasCollection('name', Users::Name, 'email', Users::Email);
   $mapCollection = buildAliasesMapCollection($data, $aliases);

   expect($mapCollection)->toBeInstanceOf(MapCollection::class)
        ->and($mapCollection->getAll())->toHaveCount(1);
});