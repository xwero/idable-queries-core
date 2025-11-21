<?php

declare(strict_types=1);

use Test\Identifiers\Arr;
use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\PlaceholderIdentifier;
use Xwero\IdableQueriesCore\PlaceholderIdentifierCollection;

test('get placeholder replacements', function (PlaceholderIdentifierCollection $collection, Closure|null $transformer, array $result) {
    expect($collection->getPlaceholderReplacements($transformer))->toBe($result);
})->with([
    'empty because single level' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users),
            new PlaceholderIdentifier('~Users:Name', Users::Name),
        ),
        null,
        [],
    ],
    'single level with transformer empty because no value' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users),
        ),
        fn(string $placeholder) => str_replace(':', '_', $placeholder),
        []
    ],
    'replacement single level with transformer' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users, 1),
        ),
        fn(string $placeholder) => str_replace(':', '_', $placeholder),
        ['~Users:Users' => '~Users_Users']
    ],
    'replacement PlaceholderIdentifierCollection' => [
      new PlaceholderIdentifierCollection(
          new PlaceholderIdentifier(
              ':Arr:Test',
              Arr::Test,
              new PlaceholderIdentifierCollection(
                  new PlaceholderIdentifier(':Arr:Test_0', Arr::Test, 1, '(', ','),
                  new PlaceholderIdentifier(':Arr:Test_1', Arr::Test, 2, suffix: ')'),
              )
          ),
      ),
      null,
      [':Arr:Test' => '(:Arr:Test_0,:Arr:Test_1)'],
    ],
    'replacement PlaceholderIdentifierCollection with transformer' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier(
                ':Arr:Test',
                Arr::Test,
                new PlaceholderIdentifierCollection(
                    new PlaceholderIdentifier(':Arr:Test_0', Arr::Test, 1, '(', ','),
                    new PlaceholderIdentifier(':Arr:Test_1', Arr::Test, 2, suffix: ')'),
                )
            ),
        ),
        fn(string $placeholder) => str_replace(':', '_', $placeholder),
        [':Arr:Test' => '(_Arr_Test_0,_Arr_Test_1)'],
    ],
]);

test('get placeholders as text', function (PlaceholderIdentifierCollection $collection, Closure|null $transformer, string $result) {
    expect($collection->getPlaceholdersAsText($transformer))->toBe($result);
})->with([
    'empty because no collection items' => [
        new PlaceholderIdentifierCollection(),
        null,
        '',
    ],
    'empty because of PlaceholderIdentifierCollection value' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier(
                '~Users:Users',
                Users::Users,
                new PlaceholderIdentifierCollection()
            ),
        ),
        null,
        '',
    ],
    'empty because of PlaceholderIdentifier value' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier(
                '~Users:Users',
                Users::Users,
                new PlaceholderIdentifier('~Users:Name', Users::Name),
            ),
        ),
        null,
        '',
    ],
    'not transformed string' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier(':Arr:Test_0', Arr::Test, 1, '(', ','),
            new PlaceholderIdentifier(':Arr:Test_1', Arr::Test, 2, suffix: ')'),
        ),
        null,
        '(:Arr:Test_0,:Arr:Test_1)',
    ],
    'transformed string' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier(':Arr:Test_0', Arr::Test, 1, '(', ','),
            new PlaceholderIdentifier(':Arr:Test_1', Arr::Test, 2, suffix: ')'),
        ),
        fn(string $placeholder) => str_replace(':', '_', $placeholder),
        '(_Arr_Test_0,_Arr_Test_1)',
    ]
]);

test('get placeholder value pairs', function (PlaceholderIdentifierCollection $collection, Closure|null $transformer, array $result) {
    expect($collection->getPlaceholderValuePairs($transformer))->toBe($result);
})->with([
    'empty because no collection items' => [
        new PlaceholderIdentifierCollection(),
        null,
        []
    ],
    'empty because no value' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users),
        ),
        null,
        [],
    ],
    'single pair' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users, 1),
        ),
        null,
        ['~Users:Users' => 1],
    ],
    'single pair with transformed placeholder' => [
        new PlaceholderIdentifierCollection(
            new PlaceholderIdentifier('~Users:Users', Users::Users, 1),
        ),
        fn(string $placeholder) => str_replace(':', '_', $placeholder),
        ['~Users_Users' => 1],
    ]
]);

