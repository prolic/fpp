<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class ToScalar implements FppDeriving
{
    const VALUE = 'ToScalar';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
