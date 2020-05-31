namespace Foo\Bar {
    use DateTimeImmutable;

    marker Event;
    marker Command;

    data Person = NonPerson { DateTimeImmutable $registered, ?UserId $userId, Name[] $names, ?int $age = 18, string[] $emails = [], HumanId[] $humanIds,
        Address[] $addresses
    } | GoodPerson { UserId $userId, ?int $age = 18 };

    data Address = { string $street, int $no };

    string Name;
    float Money;
    enum Type = Master | Slave;
    bool Deleted;
    int Age;

    guid UserId;
    guid CommandId;
    guid EventId;
}
