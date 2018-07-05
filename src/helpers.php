<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

function isScalarConstructor(Constructor $constructor): bool
{
    return \in_array($constructor->name(), ['Bool', 'Bool[]', 'Float', 'Float[]', 'Int', 'Int[]', 'String', 'String[]'], true);
}

function defaultDerivingMap(): array
{
    return [
        'AggregateChanged' => new Deriving\AggregateChanged(),
        'Command' => new Deriving\Command(),
        'DomainEvent' => new Deriving\DomainEvent(),
        'Enum' => new Deriving\Enum(),
        'Equals' => new Deriving\Equals(),
        'FromArray' => new Deriving\FromArray(),
        'FromScalar' => new Deriving\FromScalar(),
        'FromString' => new Deriving\FromString(),
        'Query' => new Deriving\Query(),
        'MicroAggregateChanged' => new Deriving\MicroAggregateChanged(),
        'ToArray' => new Deriving\ToArray(),
        'ToScalar' => new Deriving\ToScalar(),
        'ToString' => new Deriving\ToString(),
        'Uuid' => new Deriving\Uuid(),
    ];
}

function defaultBuilders(): array
{
    return [
        'static_constructor' => Builder\buildStaticConstructor,
        'accessors' => Builder\buildAccessors,
        'argument_name' => Builder\buildArgumentName,
        'arguments' => Builder\buildArguments,
        'class_extends' => Builder\buildClassExtends,
        'class_keyword' => Builder\buildClassKeyword,
        'class_name' => Builder\buildClassName,
        'constructor' => Builder\buildConstructor,
        'enum_constructors' => Builder\buildEnumConstructors,
        'enum_consts' => Builder\buildEnumConsts,
        'enum_options' => Builder\buildEnumOptions,
        'equals_body' => Builder\buildEqualsBody,
        'from_array_body' => Builder\buildFromArrayBody,
        'from_scalar_body' => Builder\buildFromScalarBody,
        'from_string_body' => Builder\buildFromStringBody,
        'message_name' => Builder\buildMessageName,
        'namespace' => Builder\buildNamespace,
        'payload_validation' => Builder\buildPayloadValidation,
        'properties' => Builder\buildProperties,
        'scalar_constructor_conditions' => Builder\buildScalarConstructorConditions,
        'scalar_type' => Builder\buildScalarType,
        'setters' => Builder\buildSetters,
        'static_constructor_body' => Builder\buildStaticConstructorBody,
        'to_array_body' => Builder\buildToArrayBody,
        'to_scalar_body' => Builder\buildToScalarBody,
        'to_string_body' => Builder\buildToScalarBody,
        'traits' => Builder\buildTraits,
        'variable_name' => Builder\buildVariableName,
        'interface_name' => Builder\buildInterfaceName,
        'class_implements' => Builder\buildClassImplements,
    ];
}

function buildReferencedClass(string $namespace, string $fqcn): string
{
    $position = \strpos($fqcn, $namespace . '\\');

    if (false !== $position) {
        return \substr($fqcn, \strlen($namespace) + 1);
    }

    return '\\' . $fqcn;
}

function buildArgumentType(Argument $argument, Definition $definition, bool $withList = true): string
{
    $code = '';

    if (null === $argument->type()) {
        return $code;
    }

    if ($withList && $argument->isList() && ! $argument->nullable()) {
        return 'array';
    }

    if ($withList && $argument->isList() && $argument->nullable()) {
        return '?array';
    }

    if ($argument->isScalartypeHint()) {
        if ($argument->nullable()) {
            $code .= '?';
        }
        $code .= $argument->type();

        return $code;
    }

    $nsPosition = \strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = \substr($argument->type(), 0, $nsPosition);
        $name = \substr($argument->type(), $nsPosition + 1);
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

    $nsPosition = \strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = \substr($argument->type(), 0, $nsPosition);
        $name = \substr($argument->type(), $nsPosition + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

    if (! $collection->hasDefinition($namespace, $name)) {
        throw new \RuntimeException(\sprintf(
            'Cannot build argument constructor for %s, no definition found',
            $namespace !== '' ? $namespace . '\\' . $name : $name
        ));
    }

    $argumentDefinition = $collection->definition($namespace, $name);

    $calledClass = $namespace === $definition->namespace()
        ? $name
        : '\\' . $argument->type();

    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\Enum::VALUE:
                return "$calledClass::fromName(\${$argument->name()})";
            case Deriving\FromString::VALUE:
            case Deriving\Uuid::VALUE:
                return "$calledClass::fromString(\${$argument->name()})";
            case Deriving\FromScalar::VALUE:
                return "$calledClass::fromScalar(\${$argument->name()})";
            case Deriving\FromArray::VALUE:
                return "$calledClass::fromArray(\${$argument->name()})";
        }
    }

    throw new \RuntimeException(\sprintf(
        'Cannot build argument constructor for %s, give a scalar type or a deriving like Enum, FromString, Uuid, FromScalar, FromArray',
        $namespace !== '' ? $namespace . '\\' . $name : $name
    ));
}

function buildScalarConstructorFromPayload(Definition $definition): string
{
    return "new {$definition->name()}(\$this->payload['value'])";
}

function buildMethodBodyFromPayload(Argument $argument, Definition $definition, DefinitionCollection $collection, bool $withCache): string
{
    $argumentName = $argument->name();

    $check = '';
    if ($withCache && $argument->nullable()) {
        $check = "if (null === \$this->$argumentName && isset(\$this->payload['$argumentName'])) {";
    } elseif ($withCache && ! $argument->nullable()) {
        $check = "if (null === \$this->$argumentName) {";
    }

    if ($argument->isScalartypeHint() || null === $argument->type()) {
        if ($withCache && $argument->nullable()) {
            return <<<CODE
$check
            \$this->$argumentName = \$this->payload['$argumentName'];
        }

        return \$this->$argumentName;
CODE;
        } elseif (! $withCache && $argument->nullable()) {
            return "return \$this->payload['$argumentName'] ?? null;";
        } elseif ($withCache && ! $argument->nullable()) {
            return <<<CODE
$check
            \$this->$argumentName = \$this->payload['$argumentName'];
        }

        return \$this->$argumentName;
CODE;
        }

        return "return \$this->payload['$argumentName'];";
    }

    $nsPosition = \strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = \substr($argument->type(), 0, $nsPosition);
        $name = \substr($argument->type(), $nsPosition + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

    if (! $collection->hasDefinition($namespace, $name)) {
        throw new \RuntimeException(\sprintf(
            'Cannot build argument constructor for %s, no definition found',
            $namespace !== '' ? $namespace . '\\' . $name : $name
        ));
    }

    $argumentDefinition = $collection->definition($namespace, $name);

    $calledClass = $namespace === $definition->namespace()
        ? $name
        : '\\' . $argument->type();

    $method = '';
    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\Enum::VALUE:
                $method = 'fromName';
                break;
            case Deriving\FromScalar::VALUE:
                $method = 'fromScalar';
                break;
            case Deriving\FromString::VALUE:
            case Deriving\Uuid::VALUE:
                $method = 'fromString';
                break;
            case Deriving\FromArray::VALUE:
                $method = 'fromArray';
                break;
        }
    }

    if (empty($method)) {
        throw new \RuntimeException(\sprintf(
            'Cannot build argument constructor for %s, give a scalar type or a deriving like Enum, FromString, Uuid, FromScalar, FromArray',
            $namespace !== '' ? $namespace . '\\' . $name : $name
        ));
    }

    if ($withCache && $argument->isList() && ! $argument->nullable()) {
        return <<<CODE
$check
            \$__returnValue = [];

            foreach (\$this->payload['$argumentName'] as \$__value) {
                \$__returnValue[] = $calledClass::$method(\$__value);
            }

            \$this->$argumentName = \$__returnValue;
        }

        return \$this->$argumentName;
CODE;
    } elseif ($withCache && $argument->isList() && $argument->nullable()) {
        return <<<CODE
$check
            \$__returnValue = [];

            foreach (\$this->payload['$argumentName'] as \$__value) {
                \$__returnValue[] = $calledClass::$method(\$__value);
            }

            \$this->$argumentName = \$__returnValue;
        }

        return \$this->$argumentName;
CODE;
    } elseif ($withCache && ! $argument->isList() && ! $argument->nullable()) {
        return <<<CODE
$check
            \$this->$argumentName = $calledClass::$method(\$this->payload['$argumentName']);
        }

        return \$this->$argumentName;
CODE;
    } elseif ($withCache && ! $argument->isList() && $argument->nullable()) {
        return <<<CODE
$check
            \$this->$argumentName = isset(\$this->payload['$argumentName']) ? $calledClass::$method(\$this->payload['$argumentName']) : null;
        }

        return \$this->$argumentName;
CODE;
    } elseif (! $withCache && $argument->isList() && ! $argument->nullable()) {
        return <<<CODE
\$__returnValue = [];

        foreach (\$this->payload['$argumentName'] as \$__value) {
            \$__returnValue[] = $calledClass::$method(\$__value);
        }

        return \$__returnValue;
CODE;
    } elseif (! $withCache && $argument->isList() && $argument->nullable()) {
        return <<<CODE
if (! isset(\$this->payload['$argumentName'])) {
            return null;
        }

        \$__returnValue = [];

        foreach (\$this->payload['$argumentName'] as \$__value) {
            \$__returnValue[] = $calledClass::$method(\$__value);
        }

        return \$__returnValue;
CODE;
    } elseif ($argument->nullable()) {
        return "return isset(\$this->payload['$argumentName']) ? $calledClass::$method(\$this->payload['$argumentName']) : null;";
    }

    return "return $calledClass::$method(\$this->payload['$argumentName']);";
}

function buildMethodBodyFromAggregateId(Argument $argument, Definition $definition, DefinitionCollection $collection, bool $withCache): string
{
    $argumentName = $argument->name();

    if ($argument->isScalartypeHint() || null === $argument->type()) {
        if ($withCache) {
            return <<<CODE
if (null === \$this->$argumentName) {
            \$this->$argumentName = \$this->aggregateId();
        }

        return \$this->$argumentName;
CODE;
        }

        return 'return $this->aggregateId();';
    }

    $nsPosition = \strrpos($argument->type(), '\\');

    if (false !== $nsPosition) {
        $namespace = \substr($argument->type(), 0, $nsPosition);
        $name = \substr($argument->type(), $nsPosition + 1);
    } else {
        $namespace = '';
        $name = $argument->type();
    }

    if (! $collection->hasDefinition($namespace, $name)) {
        throw new \RuntimeException(\sprintf(
            'Cannot build argument constructor for %s, no definition found',
            $namespace !== '' ? $namespace . '\\' . $name : $name
        ));
    }

    $argumentDefinition = $collection->definition($namespace, $name);

    $calledClass = $namespace === $definition->namespace()
        ? $name
        : '\\' . $argument->type();

    foreach ($argumentDefinition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\FromString::VALUE:
            case Deriving\Uuid::VALUE:
                if ($withCache) {
                    return <<<CODE
if (null === \$this->$argumentName) {
            \$this->$argumentName = $calledClass::fromString(\$this->aggregateId());
        }

        return \$this->$argumentName;
CODE;
                }

                return "return $calledClass::fromString(\$this->aggregateId());";
            case Deriving\FromScalar::VALUE:
                if ($withCache) {
                    return <<<CODE
if (null === \$this->$argumentName) {
            \$this->$argumentName = $calledClass::fromScalar(\$this->aggregateId());
        }

        return \$this->$argumentName;
CODE;
                }

                return "return $calledClass::fromScalar(\$this->aggregateId());";
        }
    }

    throw new \RuntimeException(\sprintf(
        'Cannot build argument constructor for %s, give a scalar type hint or a deriving like Enum, FromString, Uuid, FromScalar, FromArray',
        $namespace !== '' ? $namespace . '\\' . $name : $name
    ));
}

// found on https://stackoverflow.com/questions/24316347/how-to-format-var-export-to-php5-4-array-syntax
function var_export($var, $indent = '')
{
    switch (\gettype($var)) {
        case 'string':
            return '\'' . \addcslashes($var, "\\\$\"\r\n\t\v\f") . '\'';
        case 'array':
            $indexed = \array_keys($var) === \range(0, \count($var) - 1);
            $r = [];
            foreach ($var as $key => $value) {
                $r[] = "$indent    "
                    . ($indexed ? '' : \Fpp\var_export($key) . ' => ')
                    . \Fpp\var_export($value, "$indent    ");
            }

            return "[\n" . \implode(",\n", $r) . ",\n" . $indent . ']';
        case 'boolean':
            return $var ? 'true' : 'false';
        default:
            return \var_export($var, true);
    }
}
