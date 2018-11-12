<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Deriving as FppDeriving;
use Fpp\InvalidDeriving;

abstract class AbstractDeriving implements FppDeriving
{
    public function __toString(): string
    {
        return static::VALUE;
    }

    public function equals(FppDeriving $deriving): bool
    {
        return \get_class($this) === \get_class($deriving);
    }

    public function withArguments(array $arguments): FppDeriving
    {
        throw InvalidDeriving::noArgumentsExpected(static::VALUE);
    }
}
