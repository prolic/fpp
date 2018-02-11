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
    private $typeHint;

    /**
     * @var bool|null
     */
    private $nullable;

    public function __construct(string $name, ?string $typeHint, ?bool $nullable)
    {
        $this->name = $name;
        $this->typeHint = $typeHint;
        $this->nullable = $nullable;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function typeHint(): ?string
    {
        return $this->typeHint;
    }

    public function nullable(): ?bool
    {
        return $this->nullable;
    }

    public function isScalartypeHint(): bool
    {
        switch ($this->typeHint) {
            case 'string':
            case 'int':
            case 'bool':
            case 'float':
                return true;
            default:
                return false;
        }
    }
}
