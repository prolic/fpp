<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

class Constructor
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Argument[]
     */
    private $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        if (! in_array($name, ['String', 'Bool', 'Float', 'Int'], true)
            && false === strpos($name, '\\')
        ) {
            throw new \InvalidArgumentException('No namespace given for ' . $name);
        }

        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Argument[]
     */
    public function arguments(): array
    {
        return $this->arguments;
    }
}
