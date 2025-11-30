<?php

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\NamespaceCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\Placeholder;
use Xwero\IdableQueriesCore\PlaceholderCollection;
use function Xwero\IdableQueriesCore\getIdentifierRegex;
use function Xwero\IdableQueriesCore\queryToPlaceholderCollection;

test('Happy path identifiers', function(string $query, NamespaceCollection|null $namespaces, PlaceholderCollection $result) {
    $check = queryToPlaceholderCollection($query, getIdentifierRegex(), $namespaces);
    $checkItems = $check->getAll();
    $resultItems = $result->getAll();

    expect($check)->toBeInstanceOf(PlaceholderCollection::class)
        ->and($checkItems)->toHaveSameSize($resultItems)
        ->and($checkItems[0]->identifier)->toBe($resultItems[0]->identifier)
        ->and($checkItems[0]->placeholder)->toBe($resultItems[0]->placeholder)
    ;
})->with([
    'full namespace' => [
        'SELECT ~Test\Identifiers\Users:Name FROM ~Test\Identifiers\Users:Users;',
        null,
        new PlaceholderCollection(
            new Placeholder('~Test\Identifiers\Users:Users', Users::Users),
            new Placeholder('~Test\Identifiers\Users:Name', Users::Name),
        ),
    ],
    'shortend namespace' => [
        'SELECT ~Users:Name FROM ~Users:Users;',
        getDefaultNamespace(),
        new PlaceholderCollection(
            new Placeholder('~Users:Users', Users::Users),
            new Placeholder('~Users:Name', Users::Name),
        ),
    ],
]);

test('empty', function(string $query) {
    expect(queryToPlaceholderCollection($query, getIdentifierRegex())->isEmpty())->toBeTrue();
})->with([
    'SELECT * FROM users',
    'SELECT Users:name FROM users',
    'SELECT * FROM users WHERE :Users:name',
    'SELECT * FROM users WHERE :\Users:name',
]);

test('Error return', function(string $query) {
    expect(queryToPlaceholderCollection($query, getIdentifierRegex()))->toBeInstanceOf(Error::class);
})->with([
    'SELECT ~Users:name FROM users',
]);