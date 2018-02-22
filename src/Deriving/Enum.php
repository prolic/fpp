<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;
use Fpp\Deriving as FppDeriving;
use Fpp\InvalidDeriving;

class Enum implements FppDeriving
{
    const VALUE = 'Enum';

    public function checkDefinition(Definition $definition): void
    {
        if (0 !== count($definition->conditions())) {
            throw InvalidDeriving::noConditionsExpected($definition, self::VALUE);
        }

        foreach ($definition->derivings() as $deriving) {
            if (in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }

        if (count($definition->constructors()) < 2) {
            throw InvalidDeriving::atLeastTwoConstructorsExpected($definition, self::VALUE);
        }

        foreach ($definition->constructors() as $constructor) {
            if (count($constructor->arguments()) > 0) {
                throw InvalidDeriving::exactlyZeroConstructorArgumentsExpected($definition, self::VALUE);
            }
        }
    }

    public function __toString(): string
    {
        return self::VALUE;
    }

    private function forbidsDerivings(): array
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
}
