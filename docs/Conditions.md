Please read [Simple examples](Simple%20Examples.md) first.

Conditions are useful to add further restrictions to your objects.

Conditions start using the keyword `where` and are bound to a specific constructor, so in case you have multiple constructors,
you can have specfic rules for each constructor. You can also use the default constructor, which is `_`.

Conditions are defined by:
```
| [some rule in pure PHP] => [some error message]
```

But it's way easier to understand from simple examples:

```
namespace Foo;
data FirstName = String deriving (FromString, ToString);
data LastName = String  deriving (FromString, ToString);
data Age = Int deriving (FromScalar, ToScalar);
data Person = Person { FirstName $firstName, LastName $lastName, Age $age } | Boss { FirstName $firstName, LastName $lastName } where
    Person:
        | strlen($firstName->toString()) === 0 => 'first name too short'
        | strlen($lastName->toString()) === 0 => 'last name too short'
        | $age->toScalar() < 18 => "you\'re too young, sorry"
    Boss:
        | strlen($firstName->toString()) < 5 => 'first name too short'
        | strlen($lastName->toString()) < 5 => 'last name too short';
```

And this will generate the following:

```php
class Person
{
    private $firstName;
    private $lastName;
    private $age;

    public function __construct(FirstName $firstName, LastName $lastName, Age $age)
    {
        if (strlen($firstName->toString()) === 0) {
            throw new \InvalidArgumentException('first name too short');
        }

        if (strlen($lastName->toString()) === 0) {
            throw new \InvalidArgumentException('last name too short');
        }

        if ($age->toScalar() < 18) {
            throw new \InvalidArgumentException('you\'re too young, sorry');
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
    }

    // rest of code
}

final class Boss extends Person
{
    private $firstName;
    private $lastName;

    public function __construct(FirstName $firstName, LastName $lastName)
    {
        if (strlen($firstName->toString()) < 5) {
            throw new \InvalidArgumentException('first name too short');
        }

        if (strlen($lastName->toString()) < 5) {
            throw new \InvalidArgumentException('last name too short');
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }
```

Now let's have a short look on the default constructor.
With the default constructor, you can have rules that apply to all of the constructors, without typing the rule every time.

```
// other definitions first
data Person = Person { FirstName $firstName, LastName $lastName, Age $age } | Boss { FirstName $firstName, LastName $lastName } where
    Person:
        | strlen($lastName->toString()) === 0 => 'last name too short'
        | $age->toScalar() < 18 => "you\'re too young, sorry"
    Boss:
        | strlen($lastName->toString()) < 5 => 'last name too short'
    _:
        | strlen($firstName->toString()) === 0 => 'first name too short';
```

This will generate the following:

```php
class Person
{
    private $firstName;
    private $lastName;
    private $age;

    public function __construct(FirstName $firstName, LastName $lastName, Age $age)
    {
        if (strlen($lastName->toString()) === 0) {
            throw new \InvalidArgumentException('last name too short');
        }

        if ($age->toScalar() < 18) {
            throw new \InvalidArgumentException('you\'re too young, sorry');
        }

        if (strlen($firstName->toString()) === 0) {
            throw new \InvalidArgumentException('first name too short');
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
    }

    // rest of code
}

final class Boss extends Person
{
    private $firstName;
    private $lastName;

    public function __construct(FirstName $firstName, LastName $lastName)
    {
        if (strlen($lastName->toString()) < 5) {
            throw new \InvalidArgumentException('last name too short');
        }

        if (strlen($firstName->toString()) === 0) {
            throw new \InvalidArgumentException('first name too short');
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    // rest of code
}
```

Wanna see more? check [Markers](Markers.md)

Wanna have [prooph components (http://getprooph.org/)](http://getprooph.org/) integration? check [prooph](prooph.md)
