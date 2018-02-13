namespace Composite\Foo {
    data Email = { string $e } deriving (ToString, Equals)
}
namespace Composite\Bar {
    data Age = { int $a } deriving (ScalarConvertable, Equals)
    data Name = { string $n } deriving (ToString, Equals)
    data Person = {\Model\Foo\Email $email, Name $name, Age $age} deriving (ArrayConvertable, Equals)
}
