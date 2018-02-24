# FPP

## Functional PHP Preprocessor - Immutable data type generator

### What it this?

This library can generate immutable data types based on fpp definitions, the syntax is inspired by Haskell.

### So what really is it?

Create a file and put this in it:

```console
namespace Model\Foo;

data Person = Person { string $name, ?int $age };
```

This will generate the following php code:

```php
namespace  {
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

        public function withName(string $name): self
        {
            return new self($name, $this->age);
        }
    }
}
```

### Enums?

No problem

```console
namespace MyEnum;

enum Color = Red | Blue | Green | Yellow deriving (Enum);
```

```php
$blue = new MyEnum\Blue();
var_dump($blue->equals(new MyEnum\Blue())); // true
var_dump($blue->equals(new MyEnum\Red())); // false

function (MyEnum\Color $color): string
{
    return $color::VALUE;
}
```

### Derivings

Derivings are kind of PHP's extends keyword, the following rules apply:

- Only data types can be derived
- It's possible to derive multiple times

There are 13 deriving types for now:

- AggregateChanged
- Command
- DomainEvent
- Enum
- Equals
- FromArray
- FromScalar
- FromString
- Query
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

### Usage

`php bin/fpp.php <source dir or file> <target file>`

### Wiki

[See the wiki here](https://github.com/prolic/fpp/wiki)

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
