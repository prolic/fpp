<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;

use Fpp\InvalidDeriving;

class Uuid extends AbstractDeriving
{
    public const VALUE = 'Uuid';

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

        foreach ($definition->constructors() as $constructor) {
            if (count($constructor->arguments()) > 0) {
                throw InvalidDeriving::exactlyZeroConstructorArgumentsExpected($definition, self::VALUE);
            }
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
            Query::VALUE,
            MicroAggregateChanged::VALUE,
            ToArray::VALUE,
            ToScalar::VALUE,
            ToString::VALUE,
        ];
    }
}
