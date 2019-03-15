<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;
use Fpp\InvalidDeriving;

class AggregateChanged extends AbstractDeriving
{
    public const VALUE = 'AggregateChanged';

    public function checkDefinition(Definition $definition): void
    {
        if (0 !== \count($definition->conditions())) {
            throw InvalidDeriving::noConditionsExpected($definition, self::VALUE);
        }

        foreach ($definition->derivings() as $deriving) {
            if (\in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }

        if (\count($definition->constructors()) !== 1) {
            throw InvalidDeriving::exactlyOneConstructorExpected($definition, self::VALUE);
        }

        if (0 === \count($definition->constructors()[0]->arguments())) {
            throw InvalidDeriving::atLeastOneConstructorArgumentExpected($definition, self::VALUE);
        }

        $firstArgument = $definition->constructors()[0]->arguments()[0];
        if ($firstArgument->nullable() || $firstArgument->isList()) {
            throw InvalidDeriving::invalidFirstArgumentForDeriving($definition, self::VALUE);
        }
    }

    private function forbidsDerivings(): array
    {
        return [
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
            Uuid::VALUE,
            Exception::VALUE,
        ];
    }
}
