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

class Namespace_
{
    private string $name;
    private array $imports;
    private array $types;

    public function __construct(string $namespaceName, array $imports, array $types)
    {
        $this->name = $namespaceName;
        $this->imports = $imports;
        $this->types = $types;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function imports(): array
    {
        return $this->imports;
    }

    public function types(): array
    {
        return $this->types;
    }
}
