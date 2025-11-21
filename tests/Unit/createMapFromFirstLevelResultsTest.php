<?php

declare(strict_types=1);

use Xwero\IdableQueriesCore\Error;
use function Xwero\IdableQueriesCore\createMapFromFirstLevelResults;

test('error', function (array|Error $data, string $query, string $exceptionType): void {
    $result = createMapFromFirstLevelResults($data, $query, namespaces: getDefaultNamespace());

    expect($result)->toBeInstanceOf(Error::class)
        ->and($result->exception)->toBeInstanceOf($exceptionType);
})->with([
    'error as data' => [ new Error(new Exception('test')), 'bad data' , Exception::class],
    'int keys  as data' => [[1, 2], '~Users:Name', InvalidArgumentException::class],
]);

test('empty map', function (array|Error $data, string $query){
    $result = createMapFromFirstLevelResults($data, $query, namespaces: getDefaultNamespace());

    expect($result)->toBeInstanceOf(SplObjectStorage::class)
        ->and($result->count())->toBe(0);
})->with([
    'no placeholders query' => [['name' =>'me'], 'SELECT * FROM users'],
    'bad placeholders query' => [['name' =>'me'], 'SELECT ~Users:Email FROM users'],
]);