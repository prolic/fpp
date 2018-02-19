<?php

declare(strict_types=1);

namespace Fpp;

function buildArgumentType(Argument $argument, Definition $definition): string
{
    $code = '';

    if (null === $argument->type()) {
        return $code;
    }

    if ($argument->isScalartypeHint()) {
        if ($argument->nullable()) {
            $code .= '?';
        }
        $code .= $argument->type();

        return $code;
    }

    $nsPosition = strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = substr($argument->type(), 0, $nsPosition);
        $name = substr($argument->type(), $nsPosition + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

    $returnType = $namespace === $definition->namespace()
        ? $name
        : '\\' . $argument->type();

    if ($argument->nullable()) {
        $code .= '?';
    }
    $code .= $returnType;

    return $code;
}

function buildArgumentReturnType(Argument $argument, Definition $definition): string
{
    if (null === $argument->type()) {
        return '';
    }

    return ': ' . buildArgumentType($argument, $definition);
}

function buildScalarConstructor(Definition $definition): string
{
    return "new {$definition->name()}(\$value)";
}

function buildArgumentConstructor(Argument $argument, Definition $definition, DefinitionCollection $collection): string
{
    if ($argument->isScalartypeHint() || null === $argument->type()) {
        return "\${$argument->name()}";
    }

    $nsPosition = strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = substr($argument->type(), 0, $nsPosition);
        $name = substr($argument->type(), $nsPosition + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

    if (! $collection->hasDefinition($namespace, $name)) {
        throw new \RuntimeException('Cannot build argument constructor');
    }

    $argumentDefinition = $collection->definition($namespace, $name);

    $calledClass = $namespace === $definition->namespace()
        ? $name
        : '\\' . $argument->type();

    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\Enum::VALUE:
            case Deriving\FromString::VALUE:
            case Deriving\Uuid::VALUE:
                return "$calledClass::fromString(\${$argument->name()})";
            case Deriving\FromScalar::VALUE:
                return "$calledClass::fromScalar(\${$argument->name()})";
            case Deriving\FromArray::VALUE:
                return "$calledClass::fromArray(\${$argument->name()})";
        }
    }

    throw new \RuntimeException('Cannot build argument constructor');
}

function buildScalarConstructorFromPayload(Definition $definition): string
{
    return "new {$definition->name()}(\$this->payload['value'])";
}

function buildArgumentConstructorFromPayload(Argument $argument, Definition $definition, DefinitionCollection $collection): string
{
    if ($argument->isScalartypeHint() || null === $argument->type()) {
        return "\$this->payload['{$argument->name()}']";
    }

    $nsPosition = strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = substr($argument->type(), 0, $nsPosition);
        $name = substr($argument->type(), $nsPosition + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

    if (! $collection->hasDefinition($namespace, $name)) {
        throw new \RuntimeException('Cannot build argument constructor');
    }

    $argumentDefinition = $collection->definition($namespace, $name);

    $calledClass = $namespace === $definition->namespace()
        ? $name
        : '\\' . $argument->type();

    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\Enum::VALUE:
            case Deriving\FromString::VALUE:
            case Deriving\Uuid::VALUE:
                return "$calledClass::fromString(\$this->payload['{$argument->name()}'])";
            case Deriving\FromScalar::VALUE:
                return "$calledClass::fromScalar(\$this->payload['{$argument->name()}'])";
            case Deriving\FromArray::VALUE:
                return "$calledClass::fromArray(\$this->payload['{$argument->name()}'])";
        }
    }

    throw new \RuntimeException('Cannot build argument constructor');
}

function buildProperties(Constructor $constructor): string
{
    $properties = '';

    foreach ($constructor->arguments() as $argument) {
        $properties .= '        private $' . $argument->name() . ";\n";
    }

    return ltrim($properties);
}

function buildAccessors(Definition $definition, DefinitionCollection $collection): string
{
    // domain events only have a single constructor
    $constructor = $definition->constructors()[0];
    $accessors = '';

    foreach ($constructor->arguments() as $argument) {
        $returnType = buildArgumentReturnType($argument, $definition);
        $accessors .= <<<CODE
        public function {$argument->name()}()$returnType
        {
            return \$this->{$argument->name()};
        }


CODE;
    }

    return ltrim(substr($accessors, 0, -1));
}

function buildEventAccessors(Definition $definition, DefinitionCollection $collection): string
{
    // domain events only have a single constructor
    $constructor = $definition->constructors()[0];
    $accessors = '';

    foreach ($constructor->arguments() as $argument) {
        $returnType = buildArgumentReturnType($argument, $definition);
        $argumentConstructor = buildArgumentConstructorFromPayload($argument, $definition, $collection);
        if ($argument->nullable()) {
            $check = "if (! isset(\$this->{$argument->name()}) && isset(\$this->payload['{$argument->name()}'])) {";
        } else {
            $check = "if (! isset(\$this->{$argument->name()})) {";
        }
        $accessors .= <<<CODE
        public function {$argument->name()}()$returnType
        {
            $check
                \$this->{$argument->name()} = $argumentConstructor;
            }

            return \$this->{$argument->name()};
        }


CODE;
    }

    return ltrim(substr($accessors, 0, -1));
}

function buildPayloadAccessors(Definition $definition, DefinitionCollection $collection): string
{
    // domain events only have a single constructor
    $constructor = $definition->constructors()[0];
    $accessors = '';

    foreach ($constructor->arguments() as $argument) {
        $returnType = buildArgumentReturnType($argument, $definition);
        $argumentConstructor = buildArgumentConstructorFromPayload($argument, $definition, $collection);
        if ($argument->nullable()) {
            $return = "isset(\$this->payload['{$argument->name()}']) ? $argumentConstructor : null";
        } else {
            $return = "$argumentConstructor";
        }
        $accessors .= <<<CODE
        public function {$argument->name()}()$returnType
        {
            return $return;
        }


CODE;
    }

    return ltrim(substr($accessors, 0, -1));
}

function buildMessageName(Definition $definition): string
{
    $messageName = $definition->messageName();

    if (null === $messageName) {
        $messageName = $definition->namespace() . '\\' . $definition->name();
    }

    return $messageName;
}