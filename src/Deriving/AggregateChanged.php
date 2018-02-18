<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class AggregateChanged implements FppDeriving
{
    const VALUE = 'AggregateChanged';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
