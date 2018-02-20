namespace Model\Foo\Event;

data UserRegistered2 = UserRegistered2 {string $name, string $email} deriving (DomainEvent:'user.registered');
