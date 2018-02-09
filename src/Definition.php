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
        DerivingSet $derivings = null,
        string $messageName = null
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty string');
        }

        if ($type->is(Type::DATA()) && null !== $messageName) {
            throw new \InvalidArgumentException('Message name cannot be passed to data type');
        } elseif (! $type->is(Type::DATA()) && empty($messageName)) {
            throw new \InvalidArgumentException('Message name cannot be empty string');
        }

        if (null === $derivings) {
            $derivings = new DerivingSet();
        }

        if (count($arguments) > 1 && $derivings->contains(Deriving::STRING_CONVERTER())) {
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
            $this->arguments[] = $argument;
        }

        $this->derivings = $derivings;
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

    public function derivings(): DerivingSet
    {
        return $this->derivings;
    }

    public function messageName(): ?string
    {
        return $this->messageName;
    }
}
