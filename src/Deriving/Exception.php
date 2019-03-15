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

use Fpp\ExceptionConstructor;
use Fpp\InvalidDeriving;

class Exception extends AbstractDeriving
{
    public const VALUE = 'Exception';

    private $baseClass;
    private $constructors;
    private $defaultMessage;

    public function __construct(
        string $baseClass = '\\Exception',
        array $constructors = [],
        string $defaultMessage = ''
    ) {
        $this->baseClass = $baseClass;
        $this->constructors = $constructors;
        $this->defaultMessage = $defaultMessage;
    }

    public function withBaseClass(string $baseClass): self
    {
        return new self($baseClass, $this->constructors, $this->defaultMessage);
    }

    public function withConstructors(ExceptionConstructor ...$constructors): self
    {
        return new self($this->baseClass, $constructors, $this->defaultMessage);
    }

    public function withDefaultMessage(string $defaultMessage): self
    {
        return new self($this->baseClass, $this->constructors, $defaultMessage);
    }

    public function baseClass(): string
    {
        return $this->baseClass;
    }

    public function constructors(): array
    {
        return $this->constructors;
    }

    public function defaultMessage(): string
    {
        return $this->defaultMessage;
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
