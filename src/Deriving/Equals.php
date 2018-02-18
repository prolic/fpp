<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class Equals implements FppDeriving
{
    const VALUE = 'Equals';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
