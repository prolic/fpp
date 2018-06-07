Please read [Simple examples](Simple%20Examples.md) and [Derivings](Derivings.md) first.

There are 5 deriving types for prooph messages:

- AggregateChanged
- Command
- DomainEvent
- Query
- MicroAggregateChanged

Command, DomainEvent and Query will generate what the name says, obviously.
An AggregateChanged is a special deriving for prooph/eventsourcing.
MicroAggregateChanged is another special deriving that is not extending from prooph/eventsourcing, f.e. for prooph/micro.

Enough theory, let's generate some code:

```
namespace Foo {
    data UserId = UserId deriving (Uuid);
    data Name = String deriving (ToString, FromString);
    data Age = Int deriving (ToScalar, FromScalar);
    data Email = String deriving (ToString, FromString);
    data Person = Person { Name $name, ?Age $age, Email[] $emails };
}

namespace Foo\Command {
    data CreateUser = CreateUser { UserId $userId, Person $person } deriving (Command);
}

namespace Foo\Query {
    data FindUser = FindUser { UserId $userId } deriving (Query);
}

namespace Foo\DomainEvent {
    data UserCreated = UserCreated { UserId $userId, Person $person } deriving (DomainEvent);
}
```

Now try (Micro)AggregateChanged yourself if you're into event-sourcing.
