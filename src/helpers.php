<?php

declare(strict_types=1);

namespace Fpp;

function isScalarConstructor(Constructor $constructor): bool
{
    return in_array($constructor->name(), ['String', 'Int', 'Float', 'Bool'], true);
}

function buildReferencedClass(string $namespace, string $fqcn): string
{
    if ('' === $namespace) {
        return '\\' . $fqcn;
    }

    $position = strpos($fqcn, $namespace . '\\');

    if (false !== $position) {
        return substr($fqcn, strlen($namespace) + 1);
    }

    return '\\' . $fqcn;
}

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

function buildArgumentList(Constructor $constructor, Definition $definition): string
{
    $argumentList = '';

    foreach ($constructor->arguments() as $argument) {
        if (null === $argument->type()) {
            $argumentList .= '$' . $argument->name() . ', ';
            continue;
        }

        if ($argument->nullable()) {
            $argumentList .= '?';
        }

        if ($argument->isScalartypeHint()) {
            $argumentList .= $argument->type() . ' $' . $argument->name() . ', ';
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

        $type = $namespace === $definition->namespace()
            ? $name
            : '\\' . $argument->type();

        $argumentList .= $type . ' $' . $argument->name() . ', ';
    }

    return substr($argumentList, 0, -2);
}

function buildStaticConstructorBodyConvertingToPayload(
    Constructor $constructor,
    DefinitionCollection $collection,
    bool $inclFirstArgument
): string {
    $start = 'return new self(';
    if ($inclFirstArgument) {
        $start .= "[\n                ";
    }
    $code = '';

    $addArgument = function (int $key, string $name, string $value) use ($inclFirstArgument): string {
        if (false === $inclFirstArgument && 0 === $key) {
            return "$value, [\n";
        }

        return "                '{$name}' => {$value},\n";
    };

    foreach ($constructor->arguments() as $key => $argument) {
        if ($argument->isScalartypeHint() || null === $argument->type()) {
            $code .= $addArgument($key, $argument->name(), "\${$argument->name()}");
            continue;
        }

        $position = strrpos($argument->type(), '\\');

        if (false !== $position) {
            $namespace = substr($argument->type(), 0, $position);
            $name = substr($argument->type(), $position + 1);
        } else {
            $namespace = '';
            $name = $argument->type();
        }

        if ($collection->hasDefinition($namespace, $name)) {
            $definition = $collection->definition($namespace, $name);
        } elseif ($collection->hasConstructorDefinition($argument->type())) {
            $definition = $collection->constructorDefinition($argument->type());
        } else {
            $code .= $addArgument($key, $argument->name(), "\${$argument->name()}");
            continue;
        }

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\ToArray::VALUE:
                    $code .= $addArgument($key, $argument->name(), "\${$argument->name()}->toArray()");
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $code .= $addArgument($key, $argument->name(), "\${$argument->name()}->toScalar()");
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                $code .= $addArgument($key, $argument->name(), "\${$argument->name()}->toString()");
                    continue 3;
            }
        }

        $code .= $addArgument($key, $argument->name(), "\${$argument->name()}");
    }

    return $start . ltrim($code) . '            ]);';
}

function buildPayloadValidation(
    Constructor $constructor,
    DefinitionCollection $collection,
    bool $inclFirstArgument
): string {
    $code = '';
    foreach ($constructor->arguments() as $key => $argument) {
        if (false === $inclFirstArgument && 0 === $key) {
            // ignore first argument, it's the aggregate id
            continue;
        }
        if (null === $argument->type()) {
            $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload");
            }


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && ! $argument->nullable()) {
            $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_{$argument->type()}(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a {$argument->type()}");
            }


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && $argument->nullable()) {
            $code .= <<<CODE
            if (isset(\$payload['{$argument->name()}']) && ! is_{$argument->type()}(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$argument->type()} in payload");
            }


CODE;
            continue;
        }

        $position = strrpos($argument->type(), '\\');

        if (false !== $position) {
            $namespace = substr($argument->type(), 0, $position);
            $name = substr($argument->type(), $position + 1);
        } else {
            $namespace = '';
            $name = $argument->type();
        }

        if ($collection->hasDefinition($namespace, $name)) {
            $definition = $collection->definition($namespace, $name);
        } elseif ($collection->hasConstructorDefinition($argument->type())) {
            $definition = $collection->constructorDefinition($argument->type());
        } else {
            $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload");
            }


CODE;
            continue;
        }

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\ToArray::VALUE:
                    $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
            }


CODE;
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_{$argument->type()}(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a {$argument->type()}");
            }


CODE;
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_string(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a string");
            }


CODE;
                    continue 3;
            }
        }

        $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload");
            }


CODE;
    }

    return substr($code, 12, -1);
}

function buildEqualsBody(Constructor $constructor, string $variableName, DefinitionCollection $collection): string
{
    $code = "get_class(\$this) === get_class(\$$variableName) &&\n";

    if (0 === count($constructor->arguments())) {
        $code .= "                \$this->value === \${$variableName}->value;";

        return $code;
    }

    $code = '';

    $nullCheck = function (bool $nullable, $argumentName, string $code) use ($variableName): string {
        if (! $nullable) {
            return "                $code &&\n";
        }

        return <<<CODE
                (null === \$this->$argumentName && null === \${$variableName}->$argumentName ||
                    (null !== \$this->$argumentName && null !== \${$variableName}->$argumentName &&
                    $code)
                ) &&

CODE;
    };

    foreach ($constructor->arguments() as $argument) {
        if (null === $argument->type() || $argument->isScalartypeHint()) {
            $code .= "                \$this->{$argument->name()} === \$$variableName->{$argument->name()} &&\n";
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
            $code .= "                \$this->value === \${$variableName}->value &&\n";
            continue;
        }

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\Equals::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "\$this->{$argument->name()}->equals(\${$variableName}->{$argument->name()})");
                    continue 3;
                case Deriving\ToArray::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "\$this->{$argument->name()}->toArray() === \${$variableName}->{$argument->name()}->toArray()");
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "\$this->{$argument->name()}->toScalar() === \${$variableName}->{$argument->name()}->toScalar()");
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    $code .= $nullCheck($argument->nullable(), $argument->name(), "\$this->{$argument->name()}->toString() === \${$variableName}->{$argument->name()}->toString()");
                    continue 3;
            }
        }
    }

    return 'return ' . substr($code, 16, -4) . ';';
}

function buildFromArrayBody(Constructor $constructor, Definition $definition, DefinitionCollection $collection): string
{
    $position = strrpos($constructor->name(), '\\');

    if (false !== $position) {
        $constructorNamespace = substr($constructor->name(), 0, $position);
    } else {
        $constructorNamespace = '';
    }

    $code = '';
    foreach ($constructor->arguments() as $key => $argument) {
        if (null === $argument->type()) {
            $code .= <<<CODE
            if (! isset(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array");
            }

            \${$argument->name()} = \$data['{$argument->name()}'];


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && ! $argument->nullable()) {
            $code .= <<<CODE
            if (! isset(\$data['{$argument->name()}']) || ! is_{$argument->type()}(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a {$argument->type()}");
            }

            \${$argument->name()} = \$data['{$argument->name()}'];


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && $argument->nullable()) {
            $code .= <<<CODE
            if (isset(\$data['{$argument->name()}'])) {
                if (! is_{$argument->type()}(\$data['{$argument->name()}']) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$argument->type()} in data array");
                }

                \${$argument->name()} = \$data['{$argument->name()}'];
            } else {
                \${$argument->name()} = null;
            }


CODE;
            continue;
        }

        $position = strrpos($argument->type(), '\\');

        if (false !== $position) {
            $namespace = substr($argument->type(), 0, $position);
            $name = substr($argument->type(), $position + 1);
        } else {
            $namespace = '';
            $name = $argument->type();
        }

        if ($collection->hasDefinition($namespace, $name)) {
            $argumentDefinition = $collection->definition($namespace, $name);
        } elseif ($collection->hasConstructorDefinition($argument->type())) {
            $argumentDefinition = $collection->constructorDefinition($argument->type());
        } else {
            $class = $definition->namespace();

            if ('' !== $class) {
                $class .= '\\';
            }

            $class .= $definition->name();

            throw new \RuntimeException("Cannot build fromArray for $class , unknown argument {$argument->type()} given");
        }

        if ($constructorNamespace === $namespace) {
            $argumentClass = $name;
        } else {
            $argumentClass = ('' === $namespace) ? '\\' . $name : '\\' . $namespace . '\\' . $name;
        }

        foreach ($argumentDefinition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\FromArray::VALUE:
                    $code .= <<<CODE
            if (! isset(\$data['{$argument->name()}']) || ! is_array(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
            }

            \${$argument->name()} = $argumentClass::fromArray(\$data['{$argument->name()}']);
CODE;
                    continue 3;
                case Deriving\FromScalar::VALUE:
                    $code .= <<<CODE
            if (! isset(\$data['{$argument->name()}']) || ! is_{$argument->type()}(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a {$argument->type()}");
            }

            \${$argument->name()} = $argumentClass::fromScalar(\$data['{$argument->name()}']);


CODE;
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\FromString::VALUE:
                case Deriving\Uuid::VALUE:
                    $code .= <<<CODE
            if (! isset(\$data['{$argument->name()}']) || ! is_string(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a string");
            }

            \${$argument->name()} = $argumentClass::fromString(\$data['{$argument->name()}']);


CODE;
                    continue 3;
            }
        }

        $code .= <<<CODE
            if (! isset(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array");
            }

            \${$argument->name()} = \$data['{$argument->name()}'];


CODE;
    }

    $code .= '            return new(' . buildArgumentList($constructor, $definition) . ");\n";

    return $code;
}

function buildToArrayBody(Constructor $constructor, Definition $definition, DefinitionCollection $collection): string
{
    $code = "return [\n";

    $class = $definition->namespace();

    if ('' !== $class) {
        $class .= '\\';
    }

    $class .= $definition->name();

    foreach ($constructor->arguments() as $key => $argument) {
        if ($argument->nullable()) {
            $code .= "                null === \$this->{$argument->name()} ? null : ";
        } else {
            $code .= "                ";
        }

        if ($argument->isScalartypeHint()) {
            $code .= "\$this->{$argument->name()},\n";
            continue;
        }

        $position = strrpos($argument->type(), '\\');

        if (false !== $position) {
            $namespace = substr($argument->type(), 0, $position);
            $name = substr($argument->type(), $position + 1);
        } else {
            $namespace = '';
            $name = $argument->type();
        }

        if ($collection->hasDefinition($namespace, $name)) {
            $argumentDefinition = $collection->definition($namespace, $name);
        } elseif ($collection->hasConstructorDefinition($argument->type())) {
            $argumentDefinition = $collection->constructorDefinition($argument->type());
        } else {
            throw new \RuntimeException("Cannot build ToArray for $class, no argument type hint for {$argument->type()} given");
        }

        foreach ($argumentDefinition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    $code .= "\$this->{$argument->name()}->toString(),\n";
                    continue 3;
                case Deriving\ToArray::VALUE:
                    $code .= "\$this->{$argument->name()}->toArray(),\n";
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $code .= "\$this->{$argument->name()}->toScalar(),\n";
                    continue 3;
            }
        }

        throw new \RuntimeException("Cannot build ToArray for $class, no deriving to build array or scalar for {$argument->type()} given");
    }

    $code .= "            ];\n";

    return $code;
}

function buildToScalarBody(Constructor $constructor, Definition $definition, DefinitionCollection $collection): string
{
    $argument = $constructor->arguments()[0];

    if ($argument->isScalartypeHint()) {
        return "return \$this->{$argument->name()};\n";
    }

    $position = strrpos($argument->type(), '\\');

    if (false !== $position) {
        $namespace = substr($argument->type(), 0, $position);
        $name = substr($argument->type(), $position + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

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
                return "return \$this->{$argument->name()}->toScalar();\n";
            case Deriving\Enum::VALUE:
            case Deriving\ToString::VALUE:
            case Deriving\Uuid::VALUE:
            return "return \$this->{$argument->name()}->toString();\n";
        }
    }

    throw new \RuntimeException("Cannot build ToScalar for $class, no deriving to build scalar for {$argument->type()} given");
}
