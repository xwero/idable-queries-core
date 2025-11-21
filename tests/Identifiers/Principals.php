<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\Identifier;

enum Principals : string implements Identifier
{
    case Principals = 'principal';
    case TitleId = 'tconst';
    case PersonId = 'nconst';
    case Category = 'category';
    case Order = 'ordering';
}
