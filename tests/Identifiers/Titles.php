<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\Identifier;

enum Titles : string implements Identifier
{
    case Titles = 'title';
    case Id = 'tconst';
    case Title = 'primary_title';
    case Genres = 'genres';
}
