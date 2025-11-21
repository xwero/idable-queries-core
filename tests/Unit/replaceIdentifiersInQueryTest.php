<?php

use Xwero\IdableQueriesCore\Error;
use function Xwero\IdableQueriesCore\replaceIdentifiersInQuery;

test('return Error', function($query) {
    expect(replaceIdentifiersInQuery($query))->toBeInstanceOf(Error::class);
})->with([
    '~Users:Users',
]);

test(('no replacement'), function ($query) {
    expect(replaceIdentifiersInQuery($query))->toBe($query);
})->with([
    'test',
    'User:Users',
    ':Users:Users'
]);

test('full namespace', function (string $placeholder, string $replacement) {
   expect(replaceIdentifiersInQuery($placeholder, getDefaultNamespace()))->toBe($replacement);
})->with([
    ['~Test\Identifiers\Users:Users', "users"],
    ['~Test\Identifiers\UsersBacked:Email', "e-mail"],
    ['~Test\Identifiers\Titles:Title ~Test\Identifiers\Titles:Titles', "primary_title title"],
]);

test('shortened namespace', function (string $placeholder, string $replacement) {
    expect(replaceIdentifiersInQuery($placeholder, namespaces: getDefaultNamespace()))->toBe($replacement);
})->with([
    ['~Users:Users', "users"],
    ['~UsersBacked:Email', "e-mail"],
]);

test('SQL subquery', function () {
   $result = "select jsonb_agg(result) from ( 
      select 
        primary_title as title, 
        genres,
        (
          select jsonb_agg(actor) from (
            select
              (select primaryName from person where person.nconst = principal.nconst) as name, 
              (
                  select jsonb_agg(character)
                  from principal_character
                  where principal_character.tconst = principal.tconst
                  and principal_character.nconst = principal.nconst
              ) as characters
            from principal
            where principal.tconst = title.tconst
            and category = 'actor'
            order by ordering 
            limit 10
          ) as actor
        ) as actors,
        (
          select jsonb_agg(primaryName)
          from principal, person
          where principal.tconst = title.tconst
          and person.nconst = principal.nconst
          and category = 'director'
        ) as director,
        (
          select jsonb_agg(primaryName)
          from principal, person
          where principal.tconst = title.tconst
          and person.nconst = principal.nconst
          and category = 'writer'
        ) as writer
      from title
      where tconst = 1
    ) as result;";

   $query = "select jsonb_agg(result) from ( 
      select 
        ~Titles:Title as title, 
        ~Titles:Genres,
        (
          select jsonb_agg(actor) from (
            select
              (select ~Persons:Name from ~Persons:Persons where ~Persons:Persons.~Persons:Id = ~Principals:Principals.~Principals:PersonId) as name, 
              (
                  select jsonb_agg(~PrincipalCharacters:Character)
                  from ~PrincipalCharacters:PrincipalCharacters
                  where ~PrincipalCharacters:PrincipalCharacters.~PrincipalCharacters:TitleId = ~Principals:Principals.~Principals:TitleId
                  and ~PrincipalCharacters:PrincipalCharacters.~PrincipalCharacters:PersonId = ~Principals:Principals.~Principals:PersonId
              ) as characters
            from ~Principals:Principals
            where ~Principals:Principals.~Principals:TitleId = ~Titles:Titles.~Titles:Id
            and ~Principals:Category = 'actor'
            order by ~Principals:Order 
            limit 10
          ) as actor
        ) as actors,
        (
          select jsonb_agg(~Persons:Name)
          from ~Principals:Principals, ~Persons:Persons
          where ~Principals:Principals.~Principals:TitleId = ~Titles:Titles.~Titles:Id
          and ~Persons:Persons.~Persons:Id = ~Principals:Principals.~Principals:PersonId
          and ~Principals:Category = 'director'
        ) as director,
        (
          select jsonb_agg(~Persons:Name)
          from ~Principals:Principals, ~Persons:Persons
          where ~Principals:Principals.~Principals:TitleId = ~Titles:Titles.~Titles:Id
          and ~Persons:Persons.~Persons:Id = ~Principals:Principals.~Principals:PersonId
          and ~Principals:Category = 'writer'
        ) as writer
      from ~Titles:Titles
      where ~Titles:Id = 1
    ) as result;";

    expect(replaceIdentifiersInQuery($query, namespaces: getDefaultNamespace()))->toBe($result);
});
