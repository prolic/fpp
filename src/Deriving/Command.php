<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class Command implements FppDeriving
{
    const VALUE = 'Command';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
