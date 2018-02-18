<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class Enum implements FppDeriving
{
    const VALUE = 'Enum';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
