namespace Foo {
    data Person = { string $name = '', ?int $age = 18, string[] $emails = [] };
}

namespace Foo\Bar {
    enum Color = Red | Green | Blue;
    int Age: ScalarThingy;
    bool Truth: ScalarThingy;
    float Longitude: ScalarThingy;
    string Name: ScalarThingy;
    marker ScalarThingy;
}
