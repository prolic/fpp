namespace Model {
    data Person = {string $name, ?int $age} deriving (ArrayConverter, ValueObject)
    data Length = {int $length} deriving (StringConverter, ValueObject)
}

namespace Enums {
    data Red = {}
}
