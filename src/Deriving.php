<?php

declare(strict_types=1);

namespace Fpp;

abstract class Deriving
{
    const OPTIONS = [
        Deriving\Show::class,
        Deriving\ToString::class,
        Deriving\ScalarConvertable::class,
        Deriving\ArrayConvertable::class,
        Deriving\Equals::class,
    ];

    const OPTION_VALUES = [
        'Show',
        'ToString',
        'ScalarConvertable',
        'ArrayConvertable',
        'Equals',
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

    public function equals(Deriving $other): bool
    {
        return get_class($this) === get_class($other);
    }

    public function __toString(): string
    {
        return static::VALUE;
    }
}
