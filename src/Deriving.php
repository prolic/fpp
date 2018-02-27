<?php

declare(strict_types=1);

namespace Fpp;

interface Deriving
{
    /**
     * @throws InvalidDeriving
     */
    public function checkDefinition(Definition $definition): void;

    public function __toString(): string;

    public function equals(Deriving $deriving): bool;
}
