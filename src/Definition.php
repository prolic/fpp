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
     * @var DerivingSet
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

        if ($typeString === Type\Data::VALUE
            && null !== $messageName
        ) {
            throw new \InvalidArgumentException('Message name cannot be passed to data type');
        } elseif (! $typeString === Type\Data::VALUE
            && ! $typeString === Type\Enum::VALUE
            && empty($messageName)
        ) {
            throw new \InvalidArgumentException('Message name cannot be empty string');
        }

        if ($type->sameAs(new Type\Enum()) && empty($arguments)) {
            throw new \InvalidArgumentException('Enums need at least one implementation');
        }

        if (count($arguments) > 1
            && in_array(Deriving\StringConverter::VALUE, $derivings)
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot derive from StringConverter using more than one argument for %s\\%s',
                $namespace,
                $name
            ));
        }

        $this->type = $type;
        $this->namespace = $namespace;
        $this->name = $name;

        foreach ($arguments as $argument) {
            if (! $argument instanceof Argument) {
                throw new \InvalidArgumentException('Invalid argument given, must be an instance of ' . Argument::class);
            }
            if ($argument->name() === $name) {
                throw new \InvalidArgumentException('Argument name is not allowed to be same as object name');
            }
            if ($argument->typehint() !== null
                && $typeString === Type\Enum::VALUE
            ) {
                throw new \InvalidArgumentException('Argument typehint is not allowed for enums');
            }
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
