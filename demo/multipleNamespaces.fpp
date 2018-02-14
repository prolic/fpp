namespace Model {
    data Person = {string $name, ?int $age} deriving (ToArray, Equals)
    data Length = {int $l} deriving (ToScalar)
}

namespace Color {
    data Red = {}
}
