<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Enum;

use Fpp\Type\ClassType;
use Phunkie\Types\ImmList;

class Enum
{
    private ClassType $className;
    /** @var Immlist<Constructor> */
    private ImmList $constructors;

    public function __construct(ClassType $className, ImmList $constructors)
    {
        $this->className = $className;
        $this->constructors = $constructors;
    }

    public function className(): ClassType
    {
        return $this->className;
    }

    /**
     * @return ImmList<Constructor>
     */
    public function constructors(): ImmList
    {
        return $this->constructors;
    }
}
