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

use Phunkie\Types\ImmList;

class NamespaceType
{
    private string $name;
    private ImmList $types;

    public function __construct(string $namespaceName, ImmList $types)
    {
        $this->name = $namespaceName;
        $this->types = $types;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function types(): ImmList
    {
        return $this->types;
    }
}
