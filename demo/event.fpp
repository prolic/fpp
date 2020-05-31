namespace Foo\Bar\User {

    use Foo\Bar\Event;
    use Foo\Bar\EventId;
    use Foo\Bar\UserId;
    use Foo\Bar\Name;
    use Foo\Bar\Age;

    event UserEvent : Event (EventId, UserId) = UserRegistered as user-registered { Name $name, ?Age $age}
        | UserNameUpdated as user-name-updated { Name $name }
        | UserDeleted;
}
