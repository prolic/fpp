# Functional PHP Preprocessor - Immutable data type generator

## Messages

The following message types are available:

- `command`
- `event`

Those are basically data types with some additional attributes.

### Command

Assuming we have all the basic types already defined:

```
namespace Foo;

command UserCommand : Command (CommandId) =
    RegisteredUser as registered-user { Name $name, ?Age $age}
    | UpdateUserName as update-user-name { Name $name }
    | DeleteUser;
```

A command can be defined using the `command` keyword, following by the abstract
class (here `UserCommand`) generated, and optionally also one or more marker
interfaces (here `Command`). A command can be distinguished from other commands
by it's ID (here `CommandId`), usually that's a UUID or GUID.

After the equals sign we defined here the various constructors (sub classes) and
it's parameters (if any).

The used alias `as` that you see here, f.e. `as registered-user` defines a command
type. Each generated command has a command type property and getter-method that
returns a string representation of the command type. If none provided it will
default to the fully qualified class name.

### Event

Events here are meant to be used for event sourcing, so they not only have an EventId
similar to the CommandId for commands, but they also have an aggregate ID to specify
to which aggregate root they belong. Therefor they have one argument more, but
other than that look very similar:

```
namespace Foo;

event UserEvent : Event (EventId, UserId) =
    UserRegistered as user-registered { Name $name, ?Age $age}
    | UserNameUpdated as user-name-updated { Name $name }
    | UserDeleted;
```

Actually the EventId and AggregateId (here called `UserId`) don't need to have
different types. You could reuse the same GUID implementation everywhere:

```
namespace Foo;

guid Guid;

command UserCommand : Command (Guid) =
    RegisteredUser as registered-user { Name $name, ?Age $age}
    | UpdateUserName as update-user-name { Name $name }
    | DeleteUser;

event UserEvent : Event (Guid, Guid) =
    UserRegistered as user-registered { Name $name, ?Age $age}
    | UserNameUpdated as user-name-updated { Name $name }
    | UserDeleted;
```

That's it, last but not least, you can have full [PhpStorm Integration](PhpStorm-Integration.md).
