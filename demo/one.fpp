namespace Model\Foo;

data Person = {string $name, ?int $age} deriving (ArrayConverter, ValueObject)
data Length = {int $l} deriving (StringConverter, ValueObject)
data Red = {}
