<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;

use Fpp\InvalidDeriving;

class FromArray extends AbstractDeriving
{
    public const VALUE = 'FromArray';

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

        if (count($definition->constructors()[0]->arguments()) < 2) {
            throw InvalidDeriving::atLeastTwoConstructorArgumentsExpected($definition, self::VALUE);
        }
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
