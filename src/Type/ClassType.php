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

use function Fpp\isKeyword;

class ClassType
{
    private string $name;

    public function __construct(string $name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Empty name is forbidden');
        }

        if (isKeyword($name)) {
            throw new \InvalidArgumentException("\"$name\" is a reserved PHP keyword and cannot be used as a class name");
        }

        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
