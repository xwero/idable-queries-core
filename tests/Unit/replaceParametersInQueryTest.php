<?php

declare(strict_types=1);

use Test\Identifiers\Arr;
use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\PlaceholderIdentifierCollection;
use Xwero\IdableQueriesCore\IdableParameterCollection;
use function Xwero\IdableQueriesCore\replaceParametersInQuery;

test('simple identifier', function () {
   $result = replaceParametersInQuery(
     ':Users:Name',
        new IdableParameterCollection()->add(Users::Name, 'me'),
       getDefaultNamespace(),
   );

   expect($result->query)->toBe(':Users:Name')
    ->and($result->parameters)->toBeInstanceOf(PlaceholderIdentifierCollection::class);
});

test('custom identifier', function () {
   $result = replaceParametersInQuery(
       ':Arr:Test',
       new IdableParameterCollection()->add(Arr::Test, [1,2]),
       getDefaultNamespace(),
   );
   $replacements = $result->parameters->getPlaceholderReplacements();
   $result = array_shift($replacements);

   expect($result)->toBe('(:Arr:Test_0,:Arr:Test_1)');
});