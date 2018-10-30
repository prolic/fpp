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

use Fpp\Deriving as FppDeriving;
use Fpp\InvalidDeriving;

class Enum extends AbstractDeriving
{
    public const VALUE = 'Enum';

    private $valueMapping;
    private $arguments;

    public function __construct(array $valueMapping = [], $arguments = [])
    {
        $this->valueMapping = $valueMapping;
        $this->arguments = $arguments;
    }

    public function valueMapping(): array
    {
        return $this->valueMapping;
    }

    public function withValueMapping(array $valueMapping = []): Enum
    {
        return new self($valueMapping, $this->arguments);
    }

    public function withArguments(array $arguments): FppDeriving
    {
        return new self($this->valueMapping, $arguments);
    }

    public function asValue() : bool
    {
        return \in_array('withValue', $this->arguments, true);
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

        if (\count($definition->constructors()) < 2) {
            throw InvalidDeriving::atLeastTwoConstructorsExpected($definition, self::VALUE);
        }

        $checkValueMapping = \count($this->valueMapping) > 0;

        if ($checkValueMapping && \count($this->valueMapping) !== \count($definition->constructors())) {
            throw InvalidDeriving::enumValueMappingDoesNotMatchConstructors($definition);
        }

        $definitionNamespace = $definition->namespace();
        $definitionNamespaceLength = \strlen($definitionNamespace);

        foreach ($definition->constructors() as $constructor) {
            if (\count($constructor->arguments()) > 0) {
                throw InvalidDeriving::exactlyZeroConstructorArgumentsExpected($definition, self::VALUE);
            }

            $constructorName = $constructor->name();

            if (\substr($constructorName, 0, $definitionNamespaceLength) === $definitionNamespace) {
                $constructorName = \substr($constructorName, $definitionNamespaceLength + 1);
            }

            if ($checkValueMapping && ! \array_key_exists($constructorName, $this->valueMapping)) {
                throw InvalidDeriving::enumValueMappingDoesNotMatchConstructors($definition);
            }

            if (\strpos($constructorName, '\\') !== false) {
                throw InvalidDeriving::noConstructorNamespacesAllowed($definition, self::VALUE);
            }
        }
    }

    private function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
            Command::VALUE,
            DomainEvent::VALUE,
            Query::VALUE,
            MicroAggregateChanged::VALUE,
            ToArray::VALUE,
            ToScalar::VALUE,
            ToString::VALUE,
            Uuid::VALUE,
        ];
    }
}
