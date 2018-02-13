<?php

declare(strict_types=1);

namespace Fpp;

final class Definition
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Argument[]
     */
    private $arguments = [];

    /**
     * @var []
     */
    private $derivings;

    /**
     * @var string|null
     */
    private $messageName;

    public function __construct(
        Type $type,
        string $namespace,
        string $name,
        array $arguments = [],
        array $derivings = [],
        string $messageName = null
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty string');
        }

        $typeString = (string) $type;

        switch ($typeString) {
            case Type\Data::VALUE:
                if (null !== $messageName) {
                    throw new \InvalidArgumentException('Message name cannot be passed to data type');
                }

                if (count($arguments) > 1
                    && in_array(new Deriving\ToString(), $derivings)
                ) {
                    throw new \InvalidArgumentException(sprintf(
                        'Cannot derive from ToString using more than one argument for %s\\%s',
                        $namespace,
                        $name
                    ));
                }

                if (count($arguments) > 1
                    && in_array(new Deriving\ScalarConvertable(), $derivings)
                ) {
                    throw new \InvalidArgumentException(sprintf(
                        'Cannot derive from ScalarConvertable using more than one argument for %s\\%s',
                        $namespace,
                        $name
                    ));
                }
                break;
            case Type\Enum::VALUE:
                if (null !== $messageName) {
                    throw new \InvalidArgumentException('Message name cannot be passed to enum type');
                }
                if (empty($arguments)) {
                    throw new \InvalidArgumentException('Enums need at least one implementation');
                }

                if (count($derivings) > 0) {
                    throw new \InvalidArgumentException('No derivings allowed for enum type');
                }
                break;
            case Type\Uuid::VALUE:
                if (null !== $messageName) {
                    throw new \InvalidArgumentException('Message name cannot be passed to uuid type');
                }

                if (count($arguments) > 0) {
                    throw new \InvalidArgumentException('No arguments allowed for uuid type');
                }

                if (count($derivings) > 0) {
                    throw new \InvalidArgumentException('No derivings allowed for uuid type');
                }
                break;
            default:
                // prooph message types
                if (null !== $messageName && empty($messageName)) {
                    throw new \InvalidArgumentException("Message name cannot be empty string for $typeString type");
                }

                if (count($derivings) > 0) {
                    throw new \InvalidArgumentException('No derivings allowed for enum type');
                }
                break;
        }

        $this->type = $type;
        $this->namespace = $namespace;
        $this->name = $name;

        $argumentNames = [];
        foreach ($arguments as $argument) {
            if (! $argument instanceof Argument) {
                throw new \InvalidArgumentException('Invalid argument given, must be an instance of ' . Argument::class);
            }
            if (ucfirst($argument->name()) === $name) {
                throw new \InvalidArgumentException('Argument name is not allowed to be same as object name');
            }
            if ($argument->typeHint() !== null
                && $typeString === Type\Enum::VALUE
            ) {
                throw new \InvalidArgumentException('Argument typeHint is not allowed for enums');
            }
            if (isset($argumentNames[$argument->name()])) {
                throw new \InvalidArgumentException('Duplicate argument name given');
            }
            $argumentNames[$argument->name()] = true;
            $this->arguments[] = $argument;
        }

        $this->derivings = array_unique($derivings);
        $this->messageName = $messageName;
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function namespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
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

    public function derivings(): array
    {
        return $this->derivings;
    }

    public function messageName(): ?string
    {
        return $this->messageName;
    }
}
