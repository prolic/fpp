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

trait TypeTrait
{
    private ?Namespace_ $namespace = null;
    private string $classname;
    private ImmList $markers;

    public function __construct(string $classname, ImmList $markers)
    {
        $this->classname = $classname;
        $this->markers = $markers;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    public function markers(): ImmList
    {
        return $this->markers;
    }

    public function namespace(): ?Namespace_
    {
        return $this->namespace;
    }

    public function setNamespace(Namespace_ $namespace): void
    {
        $this->namespace = $namespace;
    }
}
