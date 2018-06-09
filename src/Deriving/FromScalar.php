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
use function Fpp\isScalarConstructor;

class FromScalar extends AbstractDeriving
{
    public const VALUE = 'FromScalar';

    public function checkDefinition(Definition $definition): void
    {
        foreach ($definition->derivings() as $deriving) {
            if (\in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }

        if (\count($definition->constructors()) !== 1) {
            throw InvalidDeriving::exactlyOneConstructorExpected($definition, self::VALUE);
        }

        $constructor = $definition->constructors()[0];

        if (isScalarConstructor($constructor)) {
            return;
        }

        if (\count($constructor->arguments()) !== 1) {
            throw InvalidDeriving::exactlyOneConstructorArgumentExpected($definition, self::VALUE);
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
            MicroAggregateChanged::VALUE,
            Uuid::VALUE,
        ];
    }
}
