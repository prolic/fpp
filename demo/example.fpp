namespace Foo\Bar {

    marker Event;
    marker Human;
    data Person = NonPerson { ?UserId $userId, Name $name, ?int $age = 18, string[] $emails = [], HumanId[] $humanIds,
        Address[] $addresses
    } | NonPerson { UserId $userId, ?int $age = 18 };
    data Address = { string $street, int $no };
    string Name;
    float Money;
    enum Type = Master | Slave;
    bool Deleted;
    int Age;
    uuid UserId;
    guid HumanId;
}
