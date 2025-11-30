<?php

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\AliasCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\Placeholder;
use Xwero\IdableQueriesCore\PlaceholderCollection;
use function Xwero\IdableQueriesCore\buildLevelMap;

test('error', function () {
   $map = buildLevelMap(['one'], new PlaceholderCollection());

   expect($map)->toBeInstanceOf(Error::class);
});

test('single item map', function () {
    $map = buildLevelMap(
        ['name' => 'John Doe'],
        new PlaceholderCollection(new Placeholder('~Users:Name', Users::Name))
    );

    expect($map)->toBeInstanceOf(SplObjectStorage::class)
        ->and($map->count())->toBe(1)
        ->and($map[Users::Name])->toBe('John Doe');
});

test('alias map', function () {
    $map = buildLevelMap(
        ['cname' => 'John Doe'],
        new PlaceholderCollection(new Placeholder('~Users:Name', Users::Name)),
        new AliasCollection()->add('cname', Users::Name),
    );

    expect($map[Users::Name])->toBe('John Doe');
});