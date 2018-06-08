# FPP

## Functional PHP Preprocessor - Immutable data type generator

### What it this?

This library can generate immutable data types based on fpp definitions, the syntax is inspired by Haskell.

### YouTube Video Tutorial

[![YouTube Video Tutorial](https://i.ytimg.com/vi/MYh1_sydQ5U/hqdefault.jpg?sqp=-oaymwEXCNACELwBSFryq4qpAwkIARUAAIhCGAE=&rs=AOn4CLCtO68XORuK-gEGeTJSXdSHqY3PBQ)](https://youtu.be/MYh1_sydQ5U)

### Wiki

[See the wiki here](https://github.com/prolic/fpp/wiki)

### So what really is it?

Create a file and put this in it:

```console
namespace Model\Foo;

data Person = Person { string $name, ?int $age };
```

This will generate the following php code:

```php
namespace Model\Foo;
final class Person
{
    private $name;
    private $age;

    public function __construct(string $name, ?int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function age(): ?int
    {
        return $this->age;
    }

    public function withName(string $name): Person
    {
        return new self($name, $this->age);
    }

    public function withAge(?int $age): Person
    {
        return new self($this->name, $age);
    }
}
```

### Enums?

No problem

```console
namespace MyEnum;

data Color = Red | Blue | Green | Yellow deriving (Enum);
```

```php
$blue = Color::blue();
var_dump($blue->equals(Color::blue())); // true
var_dump($blue->equals(Color::red())); // false

function (MyEnum\Color $color): string
{
    return $color->value();
}
```

Enums with value mappings

```console
namespace MyEnum;

data Color = Red | Blue deriving (Enum) with (Red:'someThing', Blue: 13);
```

```php
var_dump(Color::red()->value()); // 'someThing'
var_dump(Color::blue()->value()); // 13
```

### Derivings

Derivings are kind of PHP's extends keyword, the following rules apply:

- It's possible to derive multiple times
- Some derivings are not compatible to each other (f.e. Command and ToArray cannot be mixed)

There are 14 deriving types for now:

- AggregateChanged
- Command
- DomainEvent
- Enum
- Equals
- FromArray
- FromScalar
- FromString
- Query
- MicroAggregateChanged (not extending from prooph/eventsourcing, f.e. for prooph/micro)
- ToArray
- ToScalar
- ToString
- Uuid

Deriving Equals + ToArray

```console
namespace Model\Foo;

data Person = Person { string $name, ?int $age } deriving (ToArray, Equals);
```

Now you can do this:

```php
$p = new Model\Foo\Person(['name' => 'sasa', 'age' => 36]);
var_dump($p->toArray()); // ['name' => 'sasa', 'age' => 36]
$p->equals($p) // true
```

### Conditions

You can add additional conditions to your code:

```
namespace Foo;
data FirstName = String deriving (FromString, ToString);
data LastName = String  deriving (FromString, ToString);
data Age = Int deriving (FromScalar, ToScalar);
data Person = Person { FirstName $firstName, LastName $lastName, Age $age } | Boss { FirstName $firstName, LastName $lastName } where
    Person:
        | strlen($lastName->toString()) === 0 => 'last name too short'
        | $age->toScalar() < 18 => "you\'re too young, sorry"
    Boss:
        | strlen($lastName->toString()) < 5 => 'last name too short'
    _:
        | strlen($firstName->toString()) === 0 => 'first name too short';
```

### Usage

`php bin/fpp.php <source dir or file>`

It will try to find your composer autoload and fetch psr-4 and psr-0 prefixes from it.
You'll get an exception, if you want to dump a class, where you have no composer autoload definition.

### Features

- [x] Create immutable data types with ease
- [x] Strict types always
- [x] Generate prooph commands
- [x] Generate prooph events
- [x] Generate prooph queries
- [x] Generate prooph aggregate changed events
- [x] Ability to switch dumper implementation for custom output
- [x] Allow composite data objects
- [x] Allow composite prooph objects
- [x] Constructor validation
- [x] Allow creating of custom constructors
- [x] Dump files according to psr-4 and psr-0 autoloading rules
- [x] Array notation for objects and scalar types
- [x] Enum value mappings
- [x] Support for conditions
