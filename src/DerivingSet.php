<?php

declare(strict_types=1);

namespace Fpp;

use MabeEnum\EnumSet;

final class DerivingSet extends EnumSet
{
    public function __construct()
    {
        parent::__construct(Deriving::class);
    }
}
