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
    private $errorMessage;

    public function __construct(string $constructor, string $code, string $errorMessage)
    {
        $this->constructor = $constructor;
        $this->code = $code;
        $this->errorMessage = $errorMessage;
    }

    public function constructor(): string
    {
        return $this->constructor;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }
}
