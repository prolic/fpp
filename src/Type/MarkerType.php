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

class MarkerType implements Type
{
    private string $classname;
    /** @var Immlist<MarkerType> */
    private ImmList $arguments;

    public function __construct(string $classname, ImmList $markers)
    {
        $this->classname = $classname;
        $this->arguments = $markers;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    /**
     * @return ImmList<MarkerType>
     */
    public function markers(): ImmList
    {
        return $this->arguments;
    }
}
