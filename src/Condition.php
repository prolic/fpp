<?php

declare(strict_types=1);

namespace Fpp;

final class Condition
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $error;

    public function __construct(string $code, string $error)
    {
        $this->code = $code;
        $this->error = $error;
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
