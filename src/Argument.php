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
    private $type;

    /**
     * @var bool
     */
    private $nullable;

    public function __construct(string $name, string $type = null, bool $nullable = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
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

    public function isScalartypeHint(): bool
    {
        return in_array($this->type, ['string', 'int', 'bool', 'float'], true);
    }
}
