<?php

declare(strict_types=1);

use function Xwero\IdableQueriesCore\isIdableQuery;

test('not idable query', function (string $query) {
    expect(isIdableQuery($query))->toBeFalse();
})->with([
    "SELECT * FROM `users",
    "SELECT * FROM `users WHERE id = ?",
    "SELECT * FROM `users WHERE id = :id",
]);

test('idable query', function (string $query) {
    expect(isIdableQuery($query))->toBeTrue();
})->with([
    "SELECT ~Users:Name FROM `users",
    "SELECT * FROM `users WHERE id = :Users:Id",
]);