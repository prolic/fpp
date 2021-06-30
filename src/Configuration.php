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
use Composer\Autoload\ClassLoader;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStreamDirectory;
use RuntimeException;

class Configuration
{
    private const availableTargets = ['composer', 'vfs'];

    private bool $useStrictTypes;
    private string $source;
    private string $target;
    private string $successMessage;
    private Closure $printer;
    private Closure $fileParser;
    private ?string $comment;
    /** @var array<class-string,TypeConfiguration> */
    private array $types;

    /** @param array<class-string,TypeConfiguration> $types */
    public function __construct(bool $useStrictTypes, string $source, string $target, string $successMessage, callable $printer, callable $fileParser, ?string $comment, array $types)
    {
        if (! \in_array($target, self::availableTargets)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Target must be one of %s, %s given',
                    \implode(' or ', self::availableTargets),
                    $target
                )
            );
        }

        $this->useStrictTypes = $useStrictTypes;
        $this->source = $source;
        $this->target = $target;
        $this->successMessage = $successMessage;
        $this->printer = Closure::fromCallable($printer);
        $this->fileParser = Closure::fromCallable($fileParser);
        $this->comment = $comment;
        $this->types = $types;
    }

    public static function fromArray(array $data): self
    {
        if (! isset($data['use_strict_types'], $data['source'], $data['target'], $data['success_msg'], $data['printer'], $data['file_parser'], $data['comment'], $data['types'])) {
            throw new InvalidArgumentException(
                'Missing keys in array configuration'
            );
        }

        return new self(
            $data['use_strict_types'],
            $data['source'],
            $data['target'],
            $data['success_msg'],
            $data['printer'],
            $data['file_parser'],
            $data['comment'],
            $data['types']
        );
    }

    public function builderFor(Type $type): ?Closure
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

    public function fromPhpValueFor(Type $type): ?Closure
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

    public function toPhpValueFor(Type $type): ?Closure
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

    public function validatorFor(Type $type): ?Closure
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

    public function validationErrorMessageFor(Type $type): ?Closure
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

    public function equalsFor(Type $type): ?Closure
    {
        $class = \get_class($type);

        if (! isset($this->types[$class])) {
            throw new RuntimeException(\sprintf(
                'No %s for %s found',
                'equals function',
                $class
            ));
        }

        return $this->types[$class]->equals();
    }

    public function useStrictTypes(): bool
    {
        return $this->useStrictTypes;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function target(): string
    {
        return $this->target;
    }

    public function locatePathFromComposer(ClassLoader $classLoader): Closure
    {
        $prefixesPsr4 = $classLoader->getPrefixesPsr4();
        $prefixesPsr0 = $classLoader->getPrefixes();

        return function (string $classname) use ($prefixesPsr4, $prefixesPsr0): string {
            return locatePsrPath($prefixesPsr4, $prefixesPsr0, $classname);
        };
    }

    public function locatePathFromVfs(vfsStreamDirectory $directory): Closure
    {
        return function (string $classname) use ($directory) {
            return $directory->url() . DIRECTORY_SEPARATOR . \strtr($classname, '\\', DIRECTORY_SEPARATOR) . '.php';
        };
    }

    public function successMessage(): string
    {
        return $this->successMessage;
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
