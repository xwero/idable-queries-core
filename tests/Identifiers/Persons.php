<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\Identifier;

enum Persons : string implements Identifier
{
    case Persons = 'person';
    case Id = 'nconst';
    case Name = 'primaryName';

}
