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
        $allowMessageName = (null === $this->messageName);

        if (empty($constructors)) {
            throw new \InvalidArgumentException('At least one constructor required');
        }

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

            if (! $deriving->fulfillsConstructorRequirements($constructors)) {
                throw $this->unfulfilledConstructorRequirements();
            }

            foreach ($derivings as $deriving2) {
                if (in_array((string) $deriving2, $deriving->forbidsDerivings(), true)) {
                    throw $this->checkForbiddenDerivings($deriving, $derivings);
                }
            }

            if (in_array((string) $deriving, [
                Deriving\AggregateChanged::VALUE,
                Deriving\Command::VALUE,
                Deriving\DomainEvent::VALUE,
                Deriving\Query::VALUE,
            ], true)) {
                $allowMessageName = true;
            }
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

    private function unfulfilledConstructorRequirements(): \InvalidArgumentException
    {
        return $this->invalid('Does not fulfill constructor requirements');
    }

    private function checkForbiddenDerivings(Deriving $deriving, array $givenDerivings): \InvalidArgumentException
    {
        foreach ($givenDerivings as $givenDeriving) {
            if (in_array((string) $givenDeriving, $deriving->forbidsDerivings(), true)) {
                throw $this->invalid('Has additional forbidden derivings');
            }
        }
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
