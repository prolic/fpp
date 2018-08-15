Generate the code for the examples here yourself.
Create a file `demo.fpp` and run `php bin/fpp.php demo.fpp`.
Make sure you have `Foo` in your `composer.json`'s autoload section.

Let's start with something really simple:

```
namespace Foo;
data Person = Person { string $name, ?int $age };
```

Or adding default values:

```
namespace Foo;
data Person = Person { string $name, int $value = 5 };
```

But you can also reuse objects:

```
namespace Foo;
data Name = String;
data Person = Person { Name $name, ?int $age };
```

Or reuse objects across namespaces:

```
namespace Foo\Bar {
    data Name = String;
}
namespace Foo {
    data Person = Person { Bar\Name $name, ?int $age };
}
```

Use array notation:

```
namespace Foo\Bar {
    data Name = String;
    data EmailAddress = String;
}
namespace Foo {
    data Person = Person { Bar\Name $name, ?int $age, EmailAddress[] $emailAddresses, string[] $nicknames };
}
```

Put comments:

```
namespace Foo\Bar {
    data Name = String;
}
// comment here
namespace Foo {
    /*
    comment there
     */
    data Person = Person { Bar\Name $name, ?int $age };
}
```

Wanna see more? check [Derivings](Derivings.md) and [Conditions](Conditions.md)
