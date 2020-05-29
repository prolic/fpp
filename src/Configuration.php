<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Closure;
use InvalidArgumentException;
use RuntimeException;

class Configuration
{
    private bool $useStrictTypes;
    private Closure $printer;
    private Closure $fileParser;
    private ?string $comment;
    /** @var array<class-string,TypeConfiguration> */
    private array $types;

    /** @param array<class-string,TypeConfiguration> $types */
    public function __construct(bool $useStrictTypes, callable $printer, callable $fileParser, ?string $comment, array $types)
    {
        $this->useStrictTypes = $useStrictTypes;
        $this->printer = Closure::fromCallable($printer);
        $this->fileParser = Closure::fromCallable($fileParser);
        $this->comment = $comment;
        $this->types = $types;
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['use_strict_types'], $data['printer'], $data['file_parser'], $data['comment'], $data['types'])) {
            throw new InvalidArgumentException(
                'Missing keys in array configuration'
            );
        }

        return new self(
            $data['use_strict_types'],
            $data['printer'],
            $data['file_parser'],
            $data['comment'],
            $data['types']
        );
    }

    public function builderFor(Type $type): Closure
    {
        $class = \get_class($type);

        if (! isset($this->types[$class])) {
            throw new RuntimeException(\sprintf(
                'No %s for %s found',
                'builder function',
                $class
            ));
        }

        return $this->types[$class]->build();
    }

    public function fromPhpValueFor(Type $type): Closure
    {
        $class = \get_class($type);

        if (! isset($this->types[$class])) {
            throw new RuntimeException(\sprintf(
                'No %s for %s found',
                'fromPhpValue function',
                $class
            ));
        }

        return $this->types[$class]->fromPhpValue();
    }

    public function toPhpValueFor(Type $type): Closure
    {
        $class = \get_class($type);

        if (! isset($this->types[$class])) {
            throw new RuntimeException(\sprintf(
                'No %s for %s found',
                'toPhpValue function',
                $class
            ));
        }

        return $this->types[$class]->toPhpValue();
    }

    public function validatorFor(Type $type): Closure
    {
        $class = \get_class($type);

        if (! isset($this->types[$class])) {
            throw new RuntimeException(\sprintf(
                'No %s for %s found',
                'validator function',
                $class
            ));
        }

        return $this->types[$class]->validator();
    }

    public function validationErrorMessageFor(Type $type): Closure
    {
        $class = \get_class($type);

        if (! isset($this->types[$class])) {
            throw new RuntimeException(\sprintf(
                'No %s for %s found',
                'validation error message function',
                $class
            ));
        }

        return $this->types[$class]->validationErrorMessage();
    }

    public function useStrictTypes(): bool
    {
        return $this->useStrictTypes;
    }

    public function printer(): Closure
    {
        return $this->printer;
    }

    public function fileParser(): Closure
    {
        return $this->fileParser;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    /** @return array<string,Type|array> */
    public function types(): array
    {
        return $this->types;
    }
}
