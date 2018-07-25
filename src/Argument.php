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

class Argument
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $type;

    /** @var bool */
    private $nullable;

    /** @var bool */
    private $isList;

    private $defaultValue;

    public function __construct(string $name, string $type = null, bool $nullable = false, bool $isList = false, $defaultValue = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->isList = $isList;
        $this->defaultValue = $defaultValue;

        if ($isList && null !== $defaultValue) {
            throw new \InvalidArgumentException(
                'Argument "' . $name . '" cannot be a list and have a default value'
            );
        }
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

    public function isScalarTypeHint(): bool
    {
        return \in_array($this->type, ['string', 'int', 'bool', 'float'], true);
    }

    public function __toString(): string
    {
        return \sprintf('%s:%s:%d:%d', $this->name, $this->type, $this->nullable, $this->isList);
    }
}
