<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;
use Fpp\Deriving as FppDeriving;
use Fpp\InvalidDeriving;

class FromString implements FppDeriving
{
    const VALUE = 'FromString';

    public function checkDefinition(Definition $definition): void
    {
        foreach ($definition->derivings() as $deriving) {
            if (in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }

        if (count($definition->constructors()) > 1) {
            throw InvalidDeriving::exactlyOneConstructorExpected($definition, self::VALUE);
        }

        $constructor = $definition->constructors()[0];

        if ('String' === $constructor->name()) {
            return;
        }

        if (count($constructor->arguments()) !== 1) {
            throw InvalidDeriving::exactlyOneConstructorArgumentExpected($definition, self::VALUE);
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
