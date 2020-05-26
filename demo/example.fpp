namespace Foo\Bar {

    marker Human;
    data Person = { ?UserId $userId, Name $name, ?int $age = 18, string[] $emails = [], HumanId[] $humanIds,
        Address[] $addresses
    };
    data Address = { string $street, int $no };
    string Name;
    float Money;
    enum Type = Master | Slave;
    bool Deleted;
    int Age;
    uuid UserId;
    guid HumanId;
}
