namespace Model {
    data Person = {string $name, ?int $age} deriving (ArrayConverter, ValueObject)
    data Length = {int $l} deriving (ScalarConverter)
}

namespace Color {
    data Red = {}
}
