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

trait TypeTrait
{
    private string $classname;
    private array $markers;

    public function __construct(string $classname, array $markers)
    {
        $this->classname = $classname;
        $this->markers = $markers;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    public function markers(): array
    {
        return $this->markers;
    }
}
