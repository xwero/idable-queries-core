<?php

declare(strict_types=1);

use Test\Identifiers\Users;
use function Xwero\IdableQueriesCore\getIdentifierFromStrings;

test('null returned', function (string $class, string $case) {
    expect(getIdentifierFromStrings($class, $case))->toBeNull();
})->with([
    'bad class' => ['Bad', 'case'],
    'bad case' => ['Test\Identifiers\Users', 'case'],
    'not an enum' => ['Test\Identifiers\NotAnIdentifier', 'case'],
    'not an identifier' => ['Test\Identifiers\NotAnIdentifier', 'case'],
]);

test('identifier returned', function(string $class, string $case) {
   expect(getIdentifierFromStrings($class, $case))->toBe(Users::Users);
})->with([
    'capitalized class' => ['Test\Identifiers\Users', 'users'],
    'capitalized case' => ['test\identifiers\users', 'Users'],
    'all capitalized' => ['Test\Identifiers\Users', 'Users'],
    'Case with trailing number' => ['Test\Identifiers\Users', 'Users1'],
]);