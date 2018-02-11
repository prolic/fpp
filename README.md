# FPP

## Functional PHP Preprocessor

### In development - API will change !!!

### What it this?

This library can generate php code based on fpp definitions, the syntax is inspired by Haskell.

### So what really is it?

Create a file and put this in it:

```console
namespace Model\Foo;

data Person = {string $name, ?int $age}
```

Then generate php code. Now you can do this:

```php
$p = \Model\Foo\Person\Person('sasa', 36);

echo \Model\Foo\Person\name($p); // sasa
echo \Model\Foo\Person\age($p); // 36

$p2 = \Model\Foo\Person\setAge($p, 37);

echo \Model\Foo\Person\age($p2); // 37

var_dump($p === $p2); // false
```

### Enums?

No problem

```console
namespace MyEnum;

enum Color = Red | Blue | Green | Yellow
```

```php
$blue = MyEnum\Blue();
var_dump($blue->equals(MyEnum\Blue())); // true
var_dump($blue->equals(MyEnum\Red())); // false

function (MyEnum\Color $color): string
{
    return $color::VALUE;
}
```

### Derivings

Derivings are kind of PHP's extends keyword, the following rules apply:

- Only data types can be derived
- It's possible to derive multiple times

There are 5 deriving types for now:

- Show (not yet implemented)
- StringConverter
- ScalarConverter
- ArrayConverter
- ValueObject

Deriving ValueObject + ArrayConverter

```console
namespace Model\Foo;

data Person = {string $name, ?int $age} deriving (ArrayConveter, ValueObject)
```

Now you can do this:

```php
$p = Person::fromArray(['name' => 'sasa', 'age' => 36]);
var_dump($p->toArray()); // ['name' => 'sasa', 'age' => 36]
$p->equals($p) // true
```

### Usage

`php bin/fpp.php <source dir or file> <target file>`

### Demo

```console
git clone https://github.com/prolic/fpp
cd fpp
composer install
php bin/fpp.php demo demo/generated.php
```

or for a single file:

```console
php bin/fpp.php demo/enum.fpp demo/generated.php
```

### Features

- [x] Create immutable data types with ease
- [x] Strict types always
- [x] Functional accessors and setters
- [x] Generate prooph commands
- [x] Generate prooph events
- [x] Generate prooph queries
- [x] Generate prooph aggregate changed events
- [x] Generate enums
- [x] Generate uuids
- [x] Ability to switch dumper implementation for custom output
- [ ] Allow creating of custom constructors
- [ ] Show deriving feature
- [ ] Make parser more robust
- [ ] More to come
