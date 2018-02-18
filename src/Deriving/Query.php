<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class Query implements FppDeriving
{
    const VALUE = 'Query';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
