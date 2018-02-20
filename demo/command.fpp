namespace Model\Foo\Command;

data RegisterUser = RegisterUser {string $name, string $email} deriving (Command:"register-user");
