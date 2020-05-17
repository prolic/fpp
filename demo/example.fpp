namespace Foo {
    data Person = { string $name = '', ?int $age = 18, string[] $emails = [] };
    uuid UserId;
    guid HumanId : Stringify;
}

namespace Foo\Bar {
    enum Color : Enum = Red | Green | Blue;
    int Age: ScalarThingy;
    bool Truth: ScalarThingy;
    float Longitude: ScalarThingy;
    string Name: ScalarThingy;
    marker ScalarThingy;
    marker Enum;
}
