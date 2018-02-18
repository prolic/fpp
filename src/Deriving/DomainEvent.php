<?php

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;

class DomainEvent implements FppDeriving
{
    const VALUE = 'DomainEvent';

    public function __toString(): string
    {
        return self::VALUE;
    }
}
