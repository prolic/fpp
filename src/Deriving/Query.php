<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;

use Fpp\InvalidDeriving;

class Query extends AbstractDeriving
{
    public const VALUE = 'Query';

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

        if (count($definition->constructors()) !== 1) {
            throw InvalidDeriving::exactlyOneConstructorExpected($definition, self::VALUE);
        }

        if (0 === count($definition->constructors()[0]->arguments())) {
            throw InvalidDeriving::atLeastOneConstructorArgumentExpected($definition, self::VALUE);
        }
    }

    private function forbidsDerivings(): array
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
            ToArray::VALUE,
            ToScalar::VALUE,
            ToString::VALUE,
            Uuid::VALUE,
        ];
    }
}
