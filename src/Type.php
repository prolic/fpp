<?php

declare(strict_types=1);

namespace Fpp;

abstract class Type
{
    const OPTIONS = [
        AggregateChanged::class,
        Data::class,
        Enum::class,
        Command::class,
        DomainEvent::class,
        Query::class,
    ];

    const OPTION_VALUES = [
        'AggregateChanged',
        'Data',
        'Enum',
        'Command',
        'DomainEvent',
        'Query',
    ];

    protected $value;

    final public function __construct()
    {
        $valid = false;

        foreach(self::OPTIONS as $value) {
            if ($this instanceof $value) {
                $valid = true;
                break;
            }
        }

        if (! $valid) {
            $self = get_class($this);
            throw new \LogicException("Invalid Type '$self' given");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function sameAs(Type $other): bool
    {
        return get_class($this) === get_class($other);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
