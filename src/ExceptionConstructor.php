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

class ExceptionConstructor
{
    private $name;
    private $arguments;
    private $message;

    public function __construct(string $name, array $arguments = [], string $message = '')
    {
        $this->name = $name;
        $this->arguments = $arguments;
        $this->message = $message;
    }

    public function withArguments(Argument ...$arguments): self
    {
        $self = new self($this->name);
        $self->message = $this->message;
        $self->arguments = $arguments;

        return $self;
    }

    public function withMessage(string $message): self
    {
        $self = new self($this->name);
        $self->arguments = $this->arguments;
        $self->message = $message;

        return $self;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function message(): string
    {
        return $this->message;
    }
}
