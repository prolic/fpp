<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
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

const buildFromScalarBody = '\Fpp\Builder\buildFromScalarBody';

function buildFromScalarBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    if (isScalarConstructor($constructor)) {
        $name = \lcfirst($definition->name());

        return "return new self(\${$name});\n";
    }

    if (0 === \count($constructor->arguments())) {
        return $placeHolder;
    }

    $argument = $constructor->arguments()[0];

    if ($argument->isScalartypeHint()) {
        return "return new self(\${$argument->name()});\n";
    }

    $position = \strrpos($constructor->name(), '\\');

    if (false !== $position) {
        $constructorNamespace = \substr($constructor->name(), 0, $position);
    } else {
        $constructorNamespace = '';
    }

    $class = $definition->namespace();

    if ('' !== $class) {
        $class .= '\\';
    }

    $class .= $definition->name();

    $position = \strrpos($argument->type(), '\\');

    $namespace = \substr($argument->type(), 0, $position);
    $name = \substr($argument->type(), $position + 1);

    if ($collection->hasDefinition($namespace, $name)) {
        $argumentDefinition = $collection->definition($namespace, $name);
    } elseif ($collection->hasConstructorDefinition($argument->type())) {
        $argumentDefinition = $collection->constructorDefinition($argument->type());
    } else {
        throw new \RuntimeException("Cannot build fromScalar for $class, unknown argument {$argument->type()} given");
    }

    if ($constructorNamespace === $namespace) {
        $argumentClass = $name;
    } else {
        $argumentClass = '\\' . $namespace . '\\' . $name;
    }

    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\FromScalar::VALUE:
            case Deriving\Enum::VALUE:
            case Deriving\FromString::VALUE:
            case Deriving\Uuid::VALUE:
                return "return new self({$argumentClass}::fromScalar(\${$argument->name()}));\n";
        }
    }

    throw new \RuntimeException("Cannot build fromScalar for $class, no needed deriving for {$argument->type()} given");
}
