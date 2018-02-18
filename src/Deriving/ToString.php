<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class ToString implements FppDeriving
{
    const VALUE = 'ToString';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
