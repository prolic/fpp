<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type;

class BoolType implements Type
{
    private string $classname;

    public function __construct(string $classname)
    {
        $this->classname = $classname;
    }

    public function classname(): string
    {
        return $this->classname;
    }
}
