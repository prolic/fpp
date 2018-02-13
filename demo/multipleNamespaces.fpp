namespace Model {
    data Person = {string $name, ?int $age} deriving (ArrayConvertable, Equals)
    data Length = {int $l} deriving (ScalarConvertable)
}

namespace Color {
    data Red = {}
}
