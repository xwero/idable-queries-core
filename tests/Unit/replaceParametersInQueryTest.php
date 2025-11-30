<?php

declare(strict_types=1);

use Test\Identifiers\Arr;
use Test\Identifiers\Users;
use Xwero\IdableQueriesCore\DirtyQuery;
use Xwero\IdableQueriesCore\IdableParameterCollection;
use function Xwero\IdableQueriesCore\replaceParametersInQuery;

test('simple identifier', function () {
   $result = replaceParametersInQuery(
     ':Users:Name',
        new IdableParameterCollection()->add(Users::Name, 'me'),
       getDefaultNamespace(),
   );

   expect($result)->toBe(false);
});

test('custom identifier', function () {
   $result = replaceParametersInQuery(
       ':Arr:Test',
       new IdableParameterCollection()->add(Arr::Test, [1,2]),
       getDefaultNamespace(),
   );

   expect($result)->toBeInstanceOf(DirtyQuery::class);

    $replacements = $result->parameters->getPlaceholderReplacements();
    $replacement = array_shift($replacements);

   expect($replacement)->toBe('(:Arr:Test_0,:Arr:Test_1)');
});