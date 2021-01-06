<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Closure;

class TypeConfiguration
{
    private ?Closure $parse;
    private ?Closure $build;
    private ?Closure $fromPhpValue;
    private ?Closure $toPhpValue;
    private ?Closure $validator;
    private ?Closure $validationErrorMessage;
    private ?Closure $equals;

    public function __construct(
        ?callable $parse,
        ?callable $build,
        ?callable $fromPhpValue,
        ?callable $toPhpValue,
        ?callable $validator,
        ?callable $validationErrorMessage,
        ?callable $equals
    ) {
        $this->parse = $parse ? Closure::fromCallable($parse) : null;
        $this->build = $build ? Closure::fromCallable($build) : null;
        $this->fromPhpValue = $fromPhpValue ? Closure::fromCallable($fromPhpValue) : null;
        $this->toPhpValue = $toPhpValue ? Closure::fromCallable($toPhpValue) : null;
        $this->validator = $validator ? Closure::fromCallable($validator) : null;
        $this->validationErrorMessage = $validationErrorMessage ? Closure::fromCallable($validationErrorMessage) : null;
        $this->equals = $equals ? Closure::fromCallable($equals) : null;
    }

    public function parse(): ?Closure
    {
        return $this->parse;
    }

    public function build(): ?Closure
    {
        return $this->build;
    }

    public function fromPhpValue(): ?Closure
    {
        return $this->fromPhpValue;
    }

    public function toPhpValue(): ?Closure
    {
        return $this->toPhpValue;
    }

    public function validator(): ?Closure
    {
        return $this->validator;
    }

    public function validationErrorMessage(): ?Closure
    {
        return $this->validationErrorMessage;
    }

    public function equals(): ?Closure
    {
        return $this->equals;
    }
}
