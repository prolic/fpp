namespace Composite\Foo {
    data Email = { string $e } deriving (StringConverter, Equals)
}
namespace Composite\Bar {
    data Age = { int $a } deriving (ScalarConverter, Equals)
    data Name = { string $n } deriving (StringConverter, Equals)
    data Person = {\Model\Foo\Email $email, Name $name, Age $age} deriving (ArrayConverter, Equals)
}
