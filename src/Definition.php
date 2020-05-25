<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Phunkie\Types\ImmList;

class Definition
{
    private string $namespace;
    private Type $type;
    private ImmList $imports;

    public function __construct(string $namespace, Type $type, ImmList $imports)
    {
        $this->namespace = $namespace;
        $this->type = $type;
        $this->imports = $imports;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function imports(): ImmList
    {
        return $this->imports;
    }
}
