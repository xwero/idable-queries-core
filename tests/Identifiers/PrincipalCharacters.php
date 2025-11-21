<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\Identifier;

enum PrincipalCharacters : string implements Identifier
{
    case PrincipalCharacters = 'principal_character';
    case TitleId = 'tconst';
    case PersonId = 'nconst';
    case Character = 'character';
}
