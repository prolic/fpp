<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;
use Fpp\Deriving as FppDeriving;
use Fpp\InvalidDeriving;
use function Fpp\isScalarConstructor;

class ToScalar implements FppDeriving
{
    const VALUE = 'ToScalar';

    public function checkDefinition(Definition $definition): void
    {
        foreach ($definition->derivings() as $deriving) {
            if (in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }

        foreach ($definition->constructors() as $constructor) {
            if (isScalarConstructor($constructor)) {
                continue;
            }

            if (count($constructor->arguments()) !== 1) {
                throw InvalidDeriving::exactlyOneConstructorArgumentExpected($definition, self::VALUE);
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
            Enum::VALUE,
            Query::VALUE,
            Uuid::VALUE,
        ];
    }
}
