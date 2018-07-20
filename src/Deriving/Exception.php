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

class Exception extends AbstractDeriving
{
    public const VALUE = 'Exception';

    private $parent = \Exception::class;

    public function extendsClass(string $parent): void
    {
        $this->parent = $parent;
    }

    public function parent(): string
    {
        return $this->parent;
    }

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
    }

    private function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
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
        ];
    }
}
