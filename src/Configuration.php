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
use Phunkie\Types\ImmMap;
use RuntimeException;

class Configuration
{
    private bool $useStrictTypes;
    private Closure $printer;
    private Closure $fileParser;
    private ?string $comment;
    private ImmMap $types;

    public function __construct(bool $useStrictTypes, callable $printer, callable $fileParser, ?string $comment, ImmMap $types)
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
            \ImmMap($data['types'])
        );
    }

    public function builderFor(Type $type): callable
    {
        $option = $this->types->get(\get_class($type));

        if ($option->isEmpty()) {
            throw new RuntimeException('No builder for ' . \get_class($type) . ' found');
        }

        return $option->get()->_2;
    }

    public function fromPhpValueFor(Type $type): callable
    {
        $option = $this->types->get(\get_class($type));

        if ($option->isEmpty()) {
            throw new RuntimeException('No fromPhpValue function for ' . \get_class($type) . ' found');
        }

        return $option->get()->_3;
    }

    public function toPhpValueFor(Type $type): callable
    {
        $option = $this->types->get(\get_class($type));

        if ($option->isEmpty()) {
            throw new RuntimeException('No toPhpValue function for ' . \get_class($type) . ' found');
        }

        return $option->get()->_4;
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

    public function types(): ImmMap
    {
        return $this->types;
    }
}
