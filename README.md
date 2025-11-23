# Idable queries: core package

This package provides the base functions and types for the database specific packages.

The main part of this package functions are helpers to work with the `Identifier` instances.

## Overview

Idable queries are a set of packages that are at the core a multi-database wrapper.
While you can use it as a wrapper with the same functions and types per database, the libraries provide an abstraction for the database names and parameters.
At the base of the abstraction is the `Identifier` interface. It is nothing more than a library specific name for enums.

````
enum Users implements Indentifier
{
   case Users; // It is recommended to use enun name for the table/collection/set/... to make the identifier more universal
   case Name;
}
````
A backed enum is recommended to separate database name from the enum name.

A SQL query, `SELECT name FROM users WHERE name = 'me';`, can now be written as `SELECT ~Users:Name FROM ~Users:Users WHERE ~Users:Name = :Users:Name;`.
A Redis query like `HMSET users name "Hello" email "World"` can be written as `HMSET ~Users:Users ~Users:Name :Users:Name ~Users:Email :Users:Email`.
And so on with other database types.

> **note:** The placeholders are case-insensitive, but for the best compilation path in PHP it is recommended to use capitals.

To make it easier to add multiple parameters an `CustomParameterIdentifier` attribute can be added to an `Identifier` instance.
This signals to the parameter functions that the parameter value will use a transformer to replace a single placeholder.

````
#[CustomParameterIdentfier('Xwero\IdableQueriesRedis\setParameterTransformer')]
enum Set implements Identfier
{
   case Users;
}
````
Now the Redis query can be written as `HMSET ~Users:Users :Set:Users`. 
And depending on the values in the `IdableParameterCollection` instance the query will be changed.
Each database package will have predefined transformer functions. Creating the `Identifier` instance is up to the implementers.

The libraries will have map functions that transform the result of the query in an array-like structure where the key is an `Identifier` instance.
As an example the `SELECT ~Users:Name FROM ~Users:Users WHERE ~Users:Name = :Users:Name;` query result called with the `createMapFromFirstLevelResults` function will have the `$map[Users:Name]` value me.
This prevents typos when using the results.

The libraries are build with utility in mind. That is why the main functionality is in the functions, rather than in objects.
Use as much or as little as you like.

## Functions

### addIdableParameters

This is the more dangerous version of the `collectIdableParameters` function, as it adds the values to the query without preparation.

`addIdableParameters('SELECT * FROM ~Users:Users WHERE ~Users:Name = :Users:Name;', new IdableParameterCollection(User::Name, 'me'')) ` results in `SELECT * FROM ~Users:Users WHERE ~Users:Name = me`

Used by the database system packages.

### buildAliasMap

This function gives full control over the creation of a `Map` instance. The data needs to be an associate array.
A `Map` has `Identifier` instances as keys, which makes it less likely to make typos when accessing the data.

used by `buildAliasMapCollection`

### buildAliasMapCollection

Helper function to transform a two-dimensional data array into an array of `Map` instances.

### buildLevelMap

Helper function to create a `Map` instance from an associate array.

Used by `createMapFromFirstLevelResults` and `createMapFromSecondLevelResults`.

### collectIdableParameters

This function allows the application code to prepare the parameters before adding it to the query.

Used by `replaceParametersInQuery`.

### createMapFromFirstLevelResults

Function with extra checks on top of `buildLevelMap`. 

> **note:** This is more robust function, and should be the one used in a function chain.

Used by the database system packages.

### createMapFromSecondLevelResults

When the query returns multiple items use this function to create a `MapCollection`.

Used by the database system packages.

### getIdentifierFromStrings

This function accepts the class and case as a string and insures the return is either `null` or an identifier.

It is possible to add a number to the case in the occasion the same identifier needs to be used more than once.
As an example `WHERE ~Users:Name = :Users:Name1 AND ~Users:Name != :Users:Name2`

Used by `queryToPlaceholderIdentifierCollection`.

### getIdentifierRegex

This function gets the default regex or a custom one from the `IDENTIFIER_REGEX` environment variable.

### getParameterRegex

This function get the default regex or a custom one from the `PARAMETER_REGEX` environment variable.

> **note:** Both regexes should have a colon as divider between the class and the case.

### queryStringFromIdentifier

Gets a backed enum value or a lower string basic enum name.

Used by `buildlevelmap` and `PlaceholderReplacementCollection`.

### queryToPlaceholderIdentifierCollection

This function extracts the strings that match the identifier regex and makes identifiers from the strings.

An error is returned when placeholders are found but the collections is empty. the most likely cause is a wrong namespace.
It is not possible to return an error when only one or a few namespaces are wrong. 
To prevent unexpected results it is best to use the namespaces argument when the functions provide it. 

> **note:** The collection is sorted from largest string size to smallest string size of the placeholders. This is to prevent bad replacements.

Used by `addQueryParameters`, `collectQueryParameters`, `createMapFromFirstLevelResults`, `createMapFromSecondLevelResults` and `replaceIdentifiersInQuery`.

### replaceIdentifiersInQuery

Replaces the identifier placeholders from the query with the database names.

Used by the database packages.

### replaceParametersInQuery

Replaces the parameter placeholders where the data isn't matched by query placeholder.
`:Arr:Test` will become `(:Arr:Test_0,:Arr:Test_1)` when used in the PDO package. 
The number of placeholders is based on the data in the `IdableParameterCollection`.

A `$placeholderTransformer` closure can be added to make sure the placeholders don't contain characters the database doesn't recognize. 

Used by the database packages.

### runChain

When using a PHP version under 8.5, this function can be used to have a similar experience than with the pipe operator.

````
$query = 'SELECT ~Users:Name, ~Users:Email FROM ~Users:Users WHERE ~Users:Id = :UsersId';
$namespaces = new BaseNamespaceCollection('Test\Identifiers');
$result = replaceIdentifiersInQuery($query, $namespaces)
  |> (fn($query) => replaceParametersInQuery($query, new IdableParameterCollection(Users::Id, 1), $namespaces))
  |> (fn($queryAndParameters) => someDatabaseFunction($queryAndParameters))
  |> (fn($data) => createMapFromSecondLevelResults($data, $query, namespaces: $namespaces))
  ;
  
// with runChain

$query = 'SELECT ~Users:Name, ~Users:Email FROM ~Users:Users WHERE ~Users:Id = :UsersId';
$namespaces = new BaseNamespaceCollection('Test\Identifiers');
$result = runChain(new Chain(
                fn() => replaceIdentifiersInQuery($query, $namespaces)
                fn($query) => replaceParametersInQuery($query, new IdableParameterCollection(Users::Id, 1), $namespaces),
                fn($queryAndParameters) => someDatabaseFunction($queryAndParameters),
                fn($data) => createMapFromSecondLevelResults($data, $query, namespaces: $namespaces)
              ));
````

## Types

Most of the collection types are added to make sure the content is of a certain type.

### AliasCollection

It is used to match aliases in the query return with identifiers.

The constructor only accepts `Identifier` and `string` instances.

It has the method:

- getIdentifier: finds the matching `Identifier` instance by a string value.

It extends `TypeCollection`.

### BaseCollection

Has a single array to contain the collection items. Can be used for collections with a single type.

It has the methods:

- getAll: returns array
- isEmpty: checks the array size

### BaseNamespaceCollection

It is used to make the identifier placeholders in the queries shorter.

The constructor only accepts `string` values.

Extends `BaseCollection`.

### Chain

Used to add closures to the `runChain` function.

The constructor only accepts `Closure` values.

Extends `BaseCollection`.

### CustomParameterIdentifier

This is an attribute that is added to `Identifier` objects to signal to the parameter functions a single placeholder
should be replaced with placeholders that match a data structure used by a data transformer. 

### Error

The catch-all exception type to make it easier to let them pass through a function chain.

### IdableParameter

To allow query placeholders with a trailing number this class is created get the value by `Identifier` and number.7
An example is `new IdableParameter(Users::Name, 'me', 1)`.

### IdableParameterCollection

Used to add values to the parameter identifier placeholders in the query.
`:Users:name` is replaced by me when executing the query with the collection intance, `new IdableParameterCollection(Users::Name, 'me')`.

The constructor accepts `IdentifierInterface`, `int`, `array`, `float` and `string` instances.

It has the methods:

- add: adds an `IdableParameter` instance to the collection
- findValueByIdentifierAndPlaceholder: used by the `addIdableParameters` and `collectIdableParameters` functions 

Extends `BaseCollection`.

### Identifier

The interface all the enums must implement to be used as placeholders in the queries and keys in maps.

A backed enum is recommended because it provides the best configuration.
When it is a basic enum the case name will be lowercased to replace the placeholders.

### JSONError

A convenience class were the message needs to added to create an `Error` instance with and instance of the `JSONException` as the exception.

### JSONException

The message you add to the exception is appended with the JSON error message from PHP.

### Map

Added to make the language more consistent.

### MapCollection

The constructor only allows `Map` instances.

It has the method:

- add: appends a `Map` instance to the collection.

Extends `BaseCollection`.

### NativeParameterCollectionInterface

The database packages have their implementation of this class for the functions to differentiate between the idable parameter and native parameter functionality.

### PlaceHolderIdentifier

This class binds the placeholder with the identifier, and optionally the value.

The class required properties are `placeholder` and `identifier`. 
The `value` property is used for collecting the data that a function passes on.
The `prefix` and `suffix` properties are used to manipulate the `placeholder` return.

It has the methods:

- getFullPlaceholder: which concatenates the `prefix`, `placeholder` and `suffix` property values.
- getCustomValue: when the ´identifier´ value has a `CustomParameterIdentifier` attribute, this method will execute the data transformer function

### PlaceHolderIdentifierCollection

The constructor only accepts `PlaceHolderIdentifier` instances.

It has the methods:

- add: appends a `PlaceHolderIdentifier` item to the collection.
- getPlaceholderReplacements: returns a flattened array in case the placeholders in the query need to be replaced.
- getPlaceholdersAsText: calls the `getFullPlaceholder` on every item of the collection to create a string.
- getPlaceholderValuePairs: returns a flattened array with the placeholders and matching values

Extends `BaseCollection`.

### TypeCollection

The base collection to be used when one type is used for the keys. 
In this library the key for most collections is an `Identifier` instance.

## Tips

- Use the namespaces argument of the functions to prevent unexpected results because of typos or changed namespaces.



