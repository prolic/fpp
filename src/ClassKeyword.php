<?php

declare(strict_types=1);

namespace Fpp;

abstract class ClassKeyword
{
    const OPTIONS = [
        'abstract' => ClassKeyword\AbstractKeyword::class,
        'final' => ClassKeyword\FinalKeyword::class,
        'none' => ClassKeyword\NoKeyword::class,
    ];

    final public function __construct()
    {
        $valid = false;

        foreach(self::OPTIONS as $value) {
            if ($this instanceof $value) {
                $valid = true;
                break;
            }
        }

        if (! $valid) {
            $self = get_class($this);
            throw new \LogicException("Invalid class keyword '$self' given");
        }
    }

    public function toString(): string
    {
        return static::VALUE;
    }
}
