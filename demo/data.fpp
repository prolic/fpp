namespace Model\Bar;

data Person = Person {string $name, ?int $age} deriving (FromArray, ToArray, Equals);
