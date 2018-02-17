<?php

declare(strict_types=1);

namespace Fpp;

abstract class Deriving
{
    const OPTIONS = [
        Deriving\AggregateChanged::class,
        Deriving\Command::class,
        Deriving\DomainEvent::class,
        Deriving\Enum::class,
        Deriving\Equals::class,
        Deriving\FromArray::class,
        Deriving\FromScalar::class,
        Deriving\FromString::class,
        Deriving\Query::class,
        Deriving\ToArray::class,
        Deriving\ToScalar::class,
        Deriving\ToString::class,
        Deriving\Uuid::class,
    ];

    const OPTION_VALUES = [
        Deriving\AggregateChanged::VALUE,
        Deriving\Command::VALUE,
        Deriving\DomainEvent::VALUE,
        Deriving\Enum::VALUE,
        Deriving\Equals::VALUE,
        Deriving\FromArray::VALUE,
        Deriving\FromScalar::VALUE,
        Deriving\FromString::VALUE,
        Deriving\Query::VALUE,
        Deriving\ToArray::VALUE,
        Deriving\ToScalar::VALUE,
        Deriving\ToString::VALUE,
        Deriving\Uuid::VALUE,
    ];

    final public function __construct()
    {
        $valid = false;

        foreach (self::OPTIONS as $value) {
            if ($this instanceof $value) {
                $valid = true;
                break;
            }
        }

        if (! $valid) {
            $self = get_class($this);
            throw new \LogicException("Invalid Deriving '$self' given");
        }
    }

    public function __toString(): string
    {
        return static::VALUE;
    }
}
