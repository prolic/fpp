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
use function Fpp\isScalarConstructor;

const buildToScalarBody = '\Fpp\Builder\buildToScalarBody';

function buildToScalarBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    if (isScalarConstructor($constructor)) {
        return 'return $this->value;';
    }

    $valid = false;

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\ToScalar())
            || $deriving->equals(new Deriving\ToString())
        ) {
            $valid = true;
            break;
        }
    }

    if (! $valid) {
        return $placeHolder;
    }

    $argument = $constructor->arguments()[0];

    if ($argument->isScalartypeHint()) {
        return "return \$this->{$argument->name()};";
    }

    $position = \strrpos($argument->type(), '\\');

    $namespace = \substr($argument->type(), 0, $position);
    $name = \substr($argument->type(), $position + 1);

    $class = $definition->namespace();

    if ('' !== $class) {
        $class .= '\\';
    }

    $class .= $definition->name();

    if ($collection->hasDefinition($namespace, $name)) {
        $argumentDefinition = $collection->definition($namespace, $name);
    } elseif ($collection->hasConstructorDefinition($argument->type())) {
        $argumentDefinition = $collection->constructorDefinition($argument->type());
    } else {
        throw new \RuntimeException("Cannot build ToScalar for $class, unknown argument {$argument->type()} given");
    }

    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\ToScalar::VALUE:
                return "return \$this->{$argument->name()}->toScalar();";
            case Deriving\Enum::VALUE:
                return "return \$this->{$argument->name()}->name();";
            case Deriving\ToString::VALUE:
            case Deriving\Uuid::VALUE:
                return "return \$this->{$argument->name()}->toString();";
        }
    }

    throw new \RuntimeException("Cannot build ToScalar for $class, no deriving to build scalar for {$argument->type()} given");
}
