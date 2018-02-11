namespace Model {
    data Person = {string $name, ?int $age} deriving (ArrayConverter, Equals)
    data Length = {int $l} deriving (ScalarConverter)
}

namespace Color {
    data Red = {}
}
