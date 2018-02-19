<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Constructor;
use Fpp\Deriving as FppDeriving;

class FromString implements FppDeriving
{
    const VALUE = 'FromString';

    public function forbidsDerivings(): array
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

    /**
     * @param Constructor[] $constructors
     * @return bool
     */
    public function fulfillsConstructorRequirements(array $constructors): bool
    {
        if (count($constructors) > 1) {
            return false;
        }

        foreach ($constructors as $constructor) {
            if (count($constructor->arguments()) > 1) {
                return false;
            }

            if (isset($constructor->arguments()[0])) {
                $argument = $constructor->arguments()[0];

                if (! $argument->isScalartypeHint()) {
                    return false;
                }

                if ($argument->type() !== 'string') {
                    return false;
                }
            } elseif ('String' !== $constructor->name()) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        return self::VALUE;
    }
}
