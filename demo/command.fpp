namespace Foo\Bar\User {

    use Foo\Bar\Command;
    use Foo\Bar\CommandId;
    use Foo\Bar\Name;
    use Foo\Bar\Age;

    command UserCommand : Command (CommandId) = RegisteredUser as registered-user { Name $name, ?Age $age}
        | UpdateUserName as update-user-name { Name $name }
        | DeleteUser;
}
