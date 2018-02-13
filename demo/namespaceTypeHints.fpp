namespace Model\Foo {
    data Name = { string $n }
}

namespace Model\Foo\Type {
    data Age = { int $a }
}

namespace Model\Foo {
    data Person = { \Model\Foo\Name $name, Type\Age $age } deriving (ArrayConvertable, Equals)
}
