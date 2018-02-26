<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;

const buildEqualsBody = '\Fpp\Builder\buildEqualsBody';

function buildEqualsBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection): string
{
    if (null === $constructor) {
        return '';
    }

    $variableName = lcfirst($definition->name());
    $code = "get_class(\$this) === get_class(\$$variableName)\n";

    if (0 === count($constructor->arguments())) {
        $code .= "                && \$this->value === \${$variableName}->value;";

        return $code;
    }

    $nullCheck = function (bool $nullable, $argumentName, string $code) use ($variableName): string {
        if (! $nullable) {
            return "                $code\n";
        }

        return <<<CODE
                && ((null === \$this->$argumentName && null === \${$variableName}->$argumentName)
                    || (null !== \$this->$argumentName && null !== \${$variableName}->$argumentName $code)
                )

CODE;
    };

    foreach ($constructor->arguments() as $argument) {
        if (null === $argument->type() || $argument->isScalartypeHint()) {
            $code .= "                && \$this->{$argument->name()} === \$$variableName->{$argument->name()}\n";
            continue;
        }

        $nsPosition = strrpos($argument->type(), '\\');

        if (false !== $nsPosition) {
            $namespace = substr($argument->type(), 0, $nsPosition);
            $name = substr($argument->type(), $nsPosition + 1);
        } else {
            $namespace = '';
            $name = $argument->type();
        }

        if ($collection->hasDefinition($namespace, $name)) {
            $definition = $collection->definition($namespace, $name);
        } elseif ($collection->hasConstructorDefinition($argument->type())) {
            $definition = $collection->constructorDefinition($argument->type());
        } else {
            $code .= "                && \$this->value === \${$variableName}->value\n";
            continue;
        }

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\Equals::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "&& \$this->{$argument->name()}->equals(\${$variableName}->{$argument->name()})");
                    continue 3;
                case Deriving\ToArray::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "&& \$this->{$argument->name()}->toArray() === \${$variableName}->{$argument->name()}->toArray()");
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "&& \$this->{$argument->name()}->toScalar() === \${$variableName}->{$argument->name()}->toScalar()");
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "&& \$this->{$argument->name()}->toString() === \${$variableName}->{$argument->name()}->toString()");
                    continue 3;
            }
        }
    }

    return 'return ' . substr($code, 0, -1) . ';';
}