<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use function Fpp\buildArgumentType;

const buildSetters = '\Fpp\Builder\buildSetters';

function buildSetters(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    if (0 === \count($constructor->arguments())) {
        return $placeHolder;
    }

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\Query())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\MicroAggregateChanged())
        ) {
            return $placeHolder;
        }
    }

    $setters = '';

    $position = \strrpos($constructor->name(), '\\');
    $namespace = \substr($constructor->name(), 0, $position);

    if ($namespace === $definition->namespace()) {
        $self = $definition->name();
    } else {
        $self = '\\' . $definition->namespace();
        if ($self !== '\\') {
            $self .= '\\';
        }
        $self .= $definition->name();
    }

    foreach ($constructor->arguments() as $key => $argument) {
        $type = buildArgumentType($argument, $definition);

        if ($type !== '') {
            $type .= ' ';
        }

        $setterName = 'with' . \ucfirst($argument->name());
        $setters .= "    public function $setterName($type\${$argument->name()}): $self\n    {\n";
        $constructorArguments = '';

        foreach ($constructor->arguments() as $key2 => $argument2) {
            if ($key !== $key2) {
                $constructorArguments .= '$this->' . $argument2->name() . ', ';
            } else {
                $constructorArguments .= '$' . $argument->name() . ', ';
            }
        }

        if ($argument->isList() && 1 === \count($constructor->arguments())) {
            $constructorArguments = '...' . $constructorArguments;
        }

        $setters .= '        return new self(' . \substr($constructorArguments, 0, -2) . ");\n    }\n\n";
    }

    return \ltrim(\substr($setters, 0, -1));
}
