<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

class Definition
{
    private string $namespace;
    private Type $type;
    private array $imports;

    public function __construct(string $namespace, Type $type, array $imports)
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

    public function imports(): array
    {
        return $this->imports;
    }
}
