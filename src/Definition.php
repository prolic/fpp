<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

class Definition
{
    /**
     * @var DefinitionType
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
     * @var MarkerReference[]
     */
    private $markers;

    /**
     * @param DefinitionType $type
     * @param string $namespace
     * @param string $name
     * @param Constructor[] $constructors
     * @param Deriving[] $derivings
     * @param Condition[] $conditions
     * @param string|null $messageName
     * @param MarkerReference[] $markers
     */
    public function __construct(
        DefinitionType $type,
        string $namespace,
        string $name,
        array $constructors = [],
        array $derivings = [],
        array $conditions = [],
        string $messageName = null,
        array $markers = []
    ) {
        $this->type = $type;
        $this->namespace = $namespace;
        $this->name = $name;

        $allowMessageName = false;

        if (empty($namespace)) {
            throw new \InvalidArgumentException('Namespace cannot be empty string');
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty string');
        }

        if (empty($constructors) && ! $this->isMarker()) {
            throw new \InvalidArgumentException('At least one constructor required');
        }

        $this->markers = $markers;

        $constructorNames = [];
        foreach ($constructors as $constructor) {
            if (isset($constructorNames[$constructor->name()])) {
                throw new \InvalidArgumentException('Duplicate constructor name given');
            }

            $constructorNames[$constructor->name()] = true;
            $this->constructors[] = $constructor;
        }

        $derivingNames = [];
        foreach ($derivings as $deriving) {
            if (isset($derivingNames[(string) $deriving])) {
                throw new \InvalidArgumentException('Duplicate deriving given');
            }

            $deriving->checkDefinition($this);

            $derivingNames[(string) $deriving] = true;
            $this->derivings[] = $deriving;

            if (\in_array((string) $deriving, [
                Deriving\AggregateChanged::VALUE,
                Deriving\Command::VALUE,
                Deriving\DomainEvent::VALUE,
                Deriving\Query::VALUE,
                Deriving\MicroAggregateChanged::VALUE,
            ], true)) {
                $allowMessageName = true;
            }
        }

        $this->conditions = $conditions;
        $this->messageName = $messageName;

        if (! $allowMessageName && ! empty($messageName)) {
            throw $this->invalid('Message name is only allowed for AggregateChanged, Command, DomainEvent or Query');
        }

        if ('' === $messageName) {
            throw $this->invalid('Message name cannot be empty string');
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

    public function isMarker(): bool
    {
        return $this->type->equals(DefinitionType::marker());
    }

    public function markers(): array
    {
        return $this->markers;
    }

    private function invalid(string $message): \InvalidArgumentException
    {
        return new \InvalidArgumentException(\sprintf(
            'Error on %s%s: %s',
            $this->namespace . '\\',
            $this->name,
            $message
        ));
    }
}
