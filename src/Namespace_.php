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

class Namespace_
{
    private string $name;
    private ImmList $imports;
    private ImmList $types;

    public function __construct(string $namespaceName, ImmList $imports, ImmList $types)
    {
        $this->name = $namespaceName;
        $this->imports = $imports;
        $this->types = $types;

        $types->map(function ($t) {
            $t->setNamespace($this);
        });
    }

    public function name(): string
    {
        return $this->name;
    }

    public function imports(): ImmList
    {
        return $this->imports;
    }

    public function addImports(ImmList $imports): void
    {
        $this->imports = $this->imports->combine($imports);
    }

    public function types(): ImmList
    {
        return $this->types;
    }
}
