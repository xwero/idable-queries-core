<?php

use Test\Identifiers\Arr;
use Test\Identifiers\Users;
use Test\Identifiers\UsersBacked;
use Xwero\IdableQueriesCore\BaseNamespaceCollection;
use Xwero\IdableQueriesCore\Error;
use Xwero\IdableQueriesCore\PlaceholderIdentifierCollection;
use Xwero\IdableQueriesCore\IdableParameterCollection;
use function Xwero\IdableQueriesCore\collectIdableParameters;

test('return Error', function (
    string                       $query,
    IdableParameterCollection    $parameters,
    BaseNamespaceCollection|null $namespaces,
) {
    expect(collectIdableParameters($query, $parameters, $namespaces))->toBeInstanceOf(Error::class);
})->with([
    'bad full namespace' => [
        ':Test\Identifiers\Users:UsersBad',
        new IdableParameterCollection()->add(Users::Users, 1),
        null,
    ],
    'bad shortened namespace' => [
        ':Users:UsersBad',
        new IdableParameterCollection()->add(Users::Users, 1),
        new BaseNamespaceCollection('Test\Identifiers'),
    ],
]);

test ('parameters', function (
    string                       $query,
    IdableParameterCollection    $parameters,
    BaseNamespaceCollection|null $namespaces,
    array                        $result,
    ) {
    $expect = collectIdableParameters($query, $parameters, $namespaces)->getPlaceholderValuePairs();

    expect($expect)->toBe($result);
})->with([
    'full namespace' => [
        ':Test\Identifiers\Users:Users',
        new IdableParameterCollection()->add(Users::Users, 1),
        null,
        [':Test\Identifiers\Users:Users' => 1]
    ],
    'shortend namespace' =>[
        ':Users:Users',
        new IdableParameterCollection()->add(Users::Users, 1),
        getDefaultNamespace(),
        [':Users:Users' => 1]
    ],
    'shortend namespace multiple replacements' =>[
        ':Users:Users, :UsersBacked:Users',
        new IdableParameterCollection()->add(Users::Users, 1)->add( UsersBacked::Users, 2),
        getDefaultNamespace(),
        [':UsersBacked:Users' => 2, ':Users:Users' => 1]
    ],
]);

test('array transformer', function () {
   $result = collectIdableParameters(
     ':Arr:Test',
       new IdableParameterCollection()->add(Arr::Test, ['me', 'metwo']),
       getDefaultNamespace(),
   );

   $resultArray = $result->getAll()[0]->value;

   expect($result)->toBeInstanceOf(PlaceholderIdentifierCollection::class)
        ->and(count($resultArray->getAll()))->toBe(2)
        ->and($resultArray->getAll()[0]->placeholder)->toBe(':Arr:Test_0')
        ->and($resultArray->getAll()[0]->value)->toBe('me')
   ;
});