<?php

declare(strict_types=1);

namespace Fpp;

interface Deriving
{
    public function forbidsDerivings(): array;

    /**
     * @param Constructor[] $constructors
     * @return bool
     */
    public function fulfillsConstructorRequirements(array $constructors): bool;

    public function __toString(): string;
}
