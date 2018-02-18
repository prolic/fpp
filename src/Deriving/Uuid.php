<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class Uuid implements FppDeriving
{
    const VALUE = 'Uuid';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
