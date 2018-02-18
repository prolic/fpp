<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class FromString implements FppDeriving
{
    const VALUE = 'FromString';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
