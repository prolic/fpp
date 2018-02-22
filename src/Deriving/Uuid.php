<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Constructor;
use Fpp\Deriving as FppDeriving;

class Uuid implements FppDeriving
{
    const VALUE = 'Uuid';

    public function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
            Command::VALUE,
            DomainEvent::VALUE,
            Enum::VALUE,
            Equals::VALUE,
            FromArray::VALUE,
            FromScalar::VALUE,
            FromString::VALUE,
            Query::VALUE,
            ToArray::VALUE,
            ToScalar::VALUE,
            ToString::VALUE,
        ];
    }

    /**
     * @param Constructor[] $constructors
     * @return bool
     */
    public function fulfillsConstructorRequirements(array $constructors): bool
    {
        if (1 !== count($constructors)) {
            return false;
        }

        $constructor = $constructors[0];

        if (0 !== count($constructor->arguments())) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        return self::VALUE;
    }
}
