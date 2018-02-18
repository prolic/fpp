<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Constructor;
use Fpp\Deriving as FppDeriving;

class ToArray implements FppDeriving
{
    const VALUE = 'ToArray';

    public function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
            Command::VALUE,
            DomainEvent::VALUE,
            Enum::VALUE,
            Query::VALUE,
            Uuid::VALUE,
        ];
    }

    /**
     * @param Constructor[] $constructors
     * @return bool
     */
    public function fulfillsConstructorRequirements(array $constructors): bool
    {
        foreach ($constructors as $constructor) {
            if (2 > count($constructor->arguments())) {
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
