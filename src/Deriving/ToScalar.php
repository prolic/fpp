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

class ToScalar extends AbstractDeriving
{
    public const VALUE = 'ToScalar';

    public function checkDefinition(Definition $definition): void
    {
        foreach ($definition->derivings() as $deriving) {
            if (\in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }

        foreach ($definition->constructors() as $constructor) {
            if (isScalarConstructor($constructor)) {
                continue;
            }

            if (\count($constructor->arguments()) !== 1) {
                throw InvalidDeriving::exactlyOneConstructorArgumentExpected($definition, self::VALUE);
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
            Query::VALUE,
            MicroAggregateChanged::VALUE,
            Uuid::VALUE,
            Exception::VALUE,
        ];
    }
}
