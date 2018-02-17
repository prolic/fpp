<?php

declare(strict_types=1);

namespace Fpp;

class Condition
{
    /**
     * @var string
     */
    private $constructor;
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $error;

    public function __construct(string $constructor, string $code, string $error)
    {
        $this->constructor = $constructor;
        $this->code = $code;
        $this->error = $error;
    }

    public function constructor(): string
    {
        return $this->constructor;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function error(): string
    {
        return $this->error;
    }
}
