<?php

declare(strict_types=1);

namespace Fpp;

final class Constructor
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
