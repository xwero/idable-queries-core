<?php

namespace Test\Identifiers;

use Xwero\IdableQueriesCore\Identifier;


enum UsersBacked : string implements Identifier
{
    case Users = 'users';
    case Name = 'name';
    case Email = 'e-mail';
}
