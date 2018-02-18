<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class FromArray implements FppDeriving
{
    const VALUE = 'FromArray';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
