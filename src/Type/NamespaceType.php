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
use Phunkie\Types\ImmList;

class NamespaceType
{
    private string $name;
    private ImmList $classes;

    public function __construct(string $namespaceName, ImmList $classes)
    {
        if (empty($namespaceName)) {
            throw new \InvalidArgumentException('Empty name is forbidden');
        }

        if (isKeyword($namespaceName)) {
            throw new \InvalidArgumentException("\"$namespaceName\" is a reserved PHP keyword and cannot be used as a namespace");
        }

        $this->name = $namespaceName;
        $this->classes = $classes;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function classes(): ImmList
    {
        return $this->classes;
    }
}
