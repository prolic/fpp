<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Constructor;
use Fpp\Deriving as FppDeriving;

class Enum implements FppDeriving
{
    const VALUE = 'Enum';

    public function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
            Command::VALUE,
            DomainEvent::VALUE,
            Query::VALUE,
            ToArray::VALUE,
            ToScalar::VALUE,
            ToString::VALUE,
            Uuid::VALUE,
        ];
    }

    /**
     * @param Constructor[] $constructors
     * @return bool
     */
    public function fulfillsConstructorRequirements(array $constructors): bool
    {
        if (count($constructors) < 2) {
            return false;
        }

        foreach ($constructors as $constructor) {
            if (count($constructor->arguments()) > 0) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return self::VALUE;
    }
}
