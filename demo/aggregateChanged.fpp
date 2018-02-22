namespace Model\Foo\Event;

data UserRegistered = UserRegisterd {string $id, ?string $name, string $email} deriving (AggregateChanged:"user-registerd");
