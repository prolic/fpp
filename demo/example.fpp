namespace Foo {
    data Person = { string $name = '', ?int $age = 18, string[] $emails = [] };
}

namespace Foo\Bar {
    enum Color = Red | Green | Blue;
    int Age;
    bool IstAlt;
    bool Truth : BoolInterface, ScalarInterface;
    float Longitude;
    string Name;
    marker Foo;
    marker Um : Foo;
}
