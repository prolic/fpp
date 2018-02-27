<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

abstract class AbstractDeriving implements FppDeriving
{
    public function __toString(): string
    {
        return static::VALUE;
    }

    public function equals(FppDeriving $deriving): bool
    {
        return get_class($this) === get_class($deriving);
    }
}
