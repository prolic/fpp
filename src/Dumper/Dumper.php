<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

interface Dumper
{
    public function dump(Definition $definition): string;
}
