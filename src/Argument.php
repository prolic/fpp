<?php

declare(strict_types=1);

namespace Fpp;

final class Argument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $typehint;

    public function __construct(string $name, ?string $typehint)
    {
        $this->name = $name;
        $this->typehint = $typehint;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function typehint(): ?string
    {
        return $this->typehint;
    }
}
