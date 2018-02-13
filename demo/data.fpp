namespace Model\Bar;

data Person = {string $name, ?int $age} deriving (ArrayConvertable, Equals)
