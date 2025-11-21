<?php

declare(strict_types=1);

use Xwero\IdableQueriesCore\Chain;
use function Xwero\IdableQueriesCore\runChain;

test('custom function', function () {
   function a(string $a, string $b) {
       return $a . $b;
   }

   $result = runChain(new Chain(fn() => a("a", "b"), fn($x) => a($x, 'c'), fn($x) => a($x, "d")));

   expect($result)->toBe("abcd");
});