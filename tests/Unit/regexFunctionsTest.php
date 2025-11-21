<?php

declare(strict_types=1);

use function Xwero\IdableQueriesCore\getIdentifierRegex;
use function Xwero\IdableQueriesCore\getParameterRegex;

test('identifier regex from environment', function () {
   $result = 'test:test';
   putenv("IDENTIFIER_REGEX=$result");

   expect(getIdentifierRegex())->toBe($result);

   putenv("IDENTIFIER_REGEX");
});

test('parameter regex from environment', function () {
    $result = 'test:test';
    putenv("PARAMETER_REGEX=$result");

    expect(getParameterRegex())->toBe($result);

    putenv("PARAMETER_REGEX");
});