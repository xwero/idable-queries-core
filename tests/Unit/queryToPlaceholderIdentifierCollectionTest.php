<?php

use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\BaseNamespaceCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\PlaceholderIdentifier;
use Xwero\IdableQueriesCore\PlaceholderIdentifierCollection;
use function Xwero\IdableQueriesCore\getIdentifierRegex;
use function Xwero\IdableQueriesCore\queryToPlaceholderIdentifierCollection;

test('Happy path identifiers', function(string $query, BaseNamespaceCollection|null $namespaces, PlaceholderIdentifierCollection $result) {
    $check = queryToPlaceholderIdentifierCollection($query, getIdentifierRegex(), $namespaces);
    $checkItems = $check->getAll();
    $resultItems = $result->getAll();

    expect($check)->toBeInstanceOf(PlaceholderIdentifierCollection::class)
        ->and($checkItems)->toHaveSameSize($resultItems)
        ->and($checkItems[0]->identifier)->toBe($resultItems[0]->identifier)
        ->and($checkItems[0]->placeholder)->toBe($resultItems[0]->placeholder)
    ;
})->with([
    'full namespace' => [
        'SELECT ~Test\Identifiers\Users:Name FROM ~Test\Identifiers\Users:Users;',
        null,
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Test\Identifiers\Users:Users', Users::Users),
            new PlaceholderIdentifier('~Test\Identifiers\Users:Name', Users::Name),
        ),
    ],
    'shortend namespace' => [
        'SELECT ~Users:Name FROM ~Users:Users;',
        getDefaultNamespace(),
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users),
            new PlaceholderIdentifier('~Users:Name', Users::Name),
        ),
    ],
]);

test('empty', function(string $query) {
    expect(queryToPlaceholderIdentifierCollection($query, getIdentifierRegex())->isEmpty())->toBeTrue();
})->with([
    'SELECT * FROM users',
    'SELECT Users:name FROM users',
    'SELECT * FROM users WHERE :Users:name',
    'SELECT * FROM users WHERE :\Users:name',
]);

test('Error return', function(string $query) {
    expect(queryToPlaceholderIdentifierCollection($query, getIdentifierRegex()))->toBeInstanceOf(Error::class);
})->with([
    'SELECT ~Users:name FROM users',
]);