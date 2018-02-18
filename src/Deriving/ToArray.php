<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class ToArray implements FppDeriving
{
    const VALUE = 'ToArray';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
