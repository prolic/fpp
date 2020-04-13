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

class EnumType
{
    private string $classname;
    /** @var Immlist<Enum\Constructor> */
    private ImmList $constructors;

    public function __construct(string $classname, ImmList $constructors)
    {
        $this->classname = $classname;
        $this->constructors = $constructors;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    /**
     * @return ImmList<Enum\Constructor>
     */
    public function constructors(): ImmList
    {
        return $this->constructors;
    }
}
