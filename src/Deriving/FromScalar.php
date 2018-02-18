<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class FromScalar implements FppDeriving
{
    const VALUE = 'FromScalar';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
