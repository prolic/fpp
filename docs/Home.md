# Functional PHP Preprocessor - Immutable data type generator

This library can generate immutable data types in PHP. The syntax is very precise.
You can type about 1/50 of the amount of code required in FPP compared to PHP.

## Basics

Every fpp-file needs to have only one namespace, declared exactly like in PHP:

```
namespace Foo;
```

But you can also have multiple namespaces in a file, using curly bracket syntax:

```
namespace Foo {
}

namespace Bar {
}
```

The namespaces you use, need to be available in your composer.json file, so FPP knows
where to store the generated files. PSR-4 and PSR-0 are supported.

Let's start with something simple: All we need are wrappers around scalar types in PHP.

Why? Consider this code in PHP:

```
class Person {
    // maybe some properties here

    public function __construct(
        string $firstName,
        string $lastName,
        int $age,
        int $amountOfChildren,
        float $longitude,
        float $latitude
    ) {
        // more code here
    }
}
```

Although we use PHP's type system, it's easy to break things. You can put first name
and last name in the wrong order, same with age and amount of children, and so on.

What we want to have, are distinct types for each property. Just like this:

```
class Person {
    // maybe some properties here

    public function __construct(
        FirstName $firstName,
        LastName $lastName,
        Age $age,
        AmountOfChildren $amountOfChildren,
        Longitude $longitude,
        Latitude $latitude
    ) {
        // more code here
    }
}
```

Just to have the `FirstName` created, FPP will generate this PHP code for you:

```
<?php

declare(strict_types=1);

namespace Foo;

final class FirstName
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(?self $other): bool
    {
        return null !== $other && $this->value === $other->value;
    }
}
```

You can imagine the amount of boilerplate you have to write to create all those wrappers.

FPP can generate you this boilerplate very quick without much typing.

```
namespace Foo;

bool IsDeleted;
float Longitude;
int Age;
string FirstName
```

Run `./vendor/bin/fpp <path to file/ dir>` and this is generated!

## Enums, Uuid's and more

PHP doesn't have an enum type, however FPP can give you a precise syntax
that will generate PHP code for you, so you have something to work with as enums.

```
namespace Foo;

enum Color = Red | Green | Blue | Yellow;
```

If you're working with ramsey/uuid a lot, you might want to have custom types for
your uuid's as well. So you can name them `UserId` instead of just Uuid.

FPP can do that for you:

```
namespace Foo;

uuid UserId;
```

In case you want Guid's, no problem:

```
namespace Foo;

guid UserId;
```

## Adding interfaces

In case you have an existing interface you want to add to your generated class,
you can do so by using the following syntax:

```
namespace Foo;

string FirstName : MyMarker;
```

This will let the generated class `Foo\FirstName` have the `MyMarker` interface
implemented. Note that if the interface has methods defined in it, that would
not be generated as well by FPP, you'll get an invalid class you cannot use.
Therefor it's mostly useful for marker interfaces only.

## Marker interfaces

A marker interface is an interface without any defined methods or constants.
Usually it is used to `mark` a class with a given interface. A good example for that
would be the `Traversable` interface in PHP.

You can define marker interfaces with the marker keyword:

```
namespace Foo;

marker MyMarker;
```

And you can extend markers from other interfaces:

```
namespace Foo;

marker MyMarker;
marker MyMarker2;
marker OtherMarker : MyMarker, MyMarker2;
```

So far for the basics, let's head to [Data Types](Data Types.md) next.
