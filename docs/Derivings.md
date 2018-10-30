Please read [Simple examples](Simple%20Examples.md) first.

Derivings are kind of PHP's extends keyword, the following rules apply:

- It's possible to derive multiple times
- Some derivings are not compatible to each other (f.e. Command and ToArray cannot be mixed)

There are 14 deriving types for now:

- AggregateChanged
- Command
- DomainEvent
- Enum / Enum(fromValue)
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

Okay, but as always, it's easier to see for yourself, so generate this code:

```
namespace Foo;
data Person = Person { string $name, ?int $age } deriving (ToArray, Equals);
```

Or create some nice enum:

```
namespace Foo;
data Color = Red | Blue | Green | Yellow deriving (Enum);
```

Even enums with value mapping:

```console
namespace Foo;
data Color = Red | Blue | Yellow deriving (Enum) with (Red:'someThing', Blue: 13, Yellow:['key' => 'value']);
```

What about uuids?

```
namespace Foo;
data UserId = UserId deriving (Uuid);
```

Wanna see more? check [Conditions](Conditions.md)

Wanna have [prooph components](http://getprooph.org/) integration? See the [prooph integration](prooph.md)
