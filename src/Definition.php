<?php

declare(strict_types=1);

namespace Fpp;

class Definition
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Constructor[]
     */
    private $constructors = [];

    /**
     * @var Deriving[]
     */
    private $derivings = [];

    /**
     * @var Condition[]
     */
    private $conditions = [];

    /**
     * @var string|null
     */
    private $messageName;

    /**
     * @param string $namespace
     * @param string $name
     * @param Constructor[] $constructors
     * @param Deriving[] $derivings
     * @param Condition[] $conditions
     * @param string|null $messageName
     */
    public function __construct(
        string $namespace,
        string $name,
        array $constructors = [],
        array $derivings = [],
        array $conditions = [],
        string $messageName = null
    ) {
        $this->namespace = $namespace;
        $this->name = $name;

        $constructorNames = [];
        foreach ($constructors as $constructor) {
            if (! $constructor instanceof Constructor) {
                throw new \InvalidArgumentException('Invalid constructor given, must be an instance of ' . Constructor::class);
            }
            if (isset($constructorNames[$constructor->name()])) {
                throw new \InvalidArgumentException('Duplicate constructor name given');
            }
            $constructorNames[$constructor->name()] = true;
            $this->constructors[] = $constructor;
        }

        $derivingNames = [];
        foreach ($derivings as $deriving) {
            if (! $deriving instanceof Deriving) {
                throw new \InvalidArgumentException('Invalid deriving given, must be an instance of ' . Deriving::class);
            }
            if (isset($derivingNames[(string) $deriving])) {
                throw new \InvalidArgumentException('Duplicate deriving given');
            }
            $derivingNames[(string) $deriving] = true;
            $this->derivings[] = $deriving;
        }

        foreach ($conditions as $condition) {
            if (! $condition instanceof Condition) {
                throw new \InvalidArgumentException('Invalid condition given, must be an instance of ' . Condition::class);
            }
            $this->conditions[] = $condition;
        }

        $this->messageName = $messageName;

        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty string');
        }

        if ('' === $messageName) {
            throw $this->invalid('Message name cannot be empty string');
        }

        $allowMessageName = (null === $this->messageName);

        foreach ($derivings as $deriving) {
            switch ((string) $deriving) {
                case Deriving\AggregateChanged::VALUE:
                case Deriving\Command::VALUE:
                case Deriving\DomainEvent::VALUE:
                case Deriving\Query::VALUE:
                    $allowMessageName = true;
                    break;
                case Deriving\Enum::VALUE:
                    if (count($constructors) < 2) {
                        throw $this->invalid('Enum need at least two constructors');
                    }
                    foreach ($constructors as $constructor) {
                        if (count($constructor->arguments()) > 0) {
                            throw $this->invalid('Enum cannot have constructor arguments');
                        }
                    }
                    break;
                case Deriving\FromArray::VALUE:
                case Deriving\ToArray::VALUE:
                    if (count($constructors) === 0) {
                        throw $this->invalid((string) $deriving . ' needs at least one constructor');
                    }
                    foreach ($constructors as $constructor) {
                        if (count($constructor->arguments()) < 2) {
                            throw $this->invalid((string) $deriving . ' constructor needs at least two arguments');
                        }
                    }
                    break;
                case Deriving\FromScalar::VALUE:
                case Deriving\FromString::VALUE:
                case Deriving\ToScalar::VALUE:
                case Deriving\ToString::VALUE:
                    if (count($constructors) === 0) {
                        throw $this->invalid((string) $deriving . ' needs at least one constructor');
                    }
                    foreach ($constructors as $constructor) {
                        if (count($constructor->arguments()) > 1) {
                            throw $this->invalid((string) $deriving . ' constructor needs exactly one argument');
                        }
                    }
                    break;
                case Deriving\Uuid::VALUE:
                    if (count($constructors) !== 1) {
                        throw $this->invalid('Uuid need exactly one constructor');
                    }
                    foreach ($constructors as $constructor) {
                        if (count($constructor->arguments()) > 0) {
                            throw $this->invalid('Uuid cannot have constructor arguments');
                        }
                    }
                    break;
            }
        }

        if (! $allowMessageName) {
            throw $this->invalid('Message name is only allowed for AggregateChanged, Command, DomainEvent or Query');
        }
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
     * @return Constructor[]
     */
    public function constructors(): array
    {
        return $this->constructors;
    }

    /**
     * @return Deriving[]
     */
    public function derivings(): array
    {
        return $this->derivings;
    }

    /**
     * @return Condition[]
     */
    public function conditions(): array
    {
        return $this->conditions;
    }

    public function messageName(): ?string
    {
        return $this->messageName;
    }

    private function invalid(string $message): \InvalidArgumentException
    {
        $namespace = '';

        if ('' !== $this->namespace) {
            $namespace = "(namespace $namespace)";
        }

        return new \InvalidArgumentException(sprintf(
            'Error on %s %s: %s',
            $this->name,
            $namespace,
            $message
        ));
    }
}
