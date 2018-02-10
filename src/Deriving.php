<?php

declare(strict_types=1);

namespace Fpp;

abstract class Deriving
{
    const OPTIONS = [
        Deriving\Show::class,
        Deriving\StringConverter::class,
        Deriving\ArrayConverter::class,
        Deriving\ValueObject::class,
    ];

    const OPTION_VALUES = [
        'Show',
        'StringConverter',
        'ArrayConverter',
        'ValueObject',
    ];

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
            throw new \LogicException("Invalid Deriving '$self' given");
        }
    }

    public function sameAs(Deriving $other): bool
    {
        return get_class($this) === get_class($other);
    }

    public function __toString(): string
    {
        return static::VALUE;
    }
}
