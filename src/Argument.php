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

class Argument
{
    private string $name;
    private ?string $type;
    private bool $nullable;
    private bool $isList;
    /** @var mixed */
    private $defaultValue;

    public function __construct(string $name, ?string $type, bool $nullable, bool $isList, $defaultValue)
    {
        if (empty($name)) {
            $name = \lcfirst($type);

            switch (true) {
                case $isList && \substr($name, -1) === 's':
                    $name .= 'es';
                    break;
                case $isList && \substr($name, -1) === 'y':
                    $name = \substr($name, 0, -1) . 'ies';
                    break;
                case $isList:
                    $name .= 's';
            }
        }

        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->isList = $isList;
        $this->defaultValue = $defaultValue;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function isList(): bool
    {
        return $this->isList;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }
}
