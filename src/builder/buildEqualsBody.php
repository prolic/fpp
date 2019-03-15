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

const buildEqualsBody = '\Fpp\Builder\buildEqualsBody';

function buildEqualsBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $build = false;

    $equalsDeriving = new Deriving\Equals();
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals($equalsDeriving)) {
            $build = true;
            break;
        }
    }

    if (! $build) {
        return $placeHolder;
    }

    $variableName = \lcfirst($definition->name());
    $isFinal = buildClassKeyword($definition, $constructor, $collection, $placeHolder) === 'final ';
    $code = '';
    $addCode = '        return ';

    if (! $isFinal) {
        $code = "if (\get_class(\$this) !== \get_class(\$$variableName)) {\n";
        $code .= "            return false;\n";
        $code .= "        }\n\n";
    }

    if (0 === \count($constructor->arguments())) {
        $code .= "        return \$this->value === \${$variableName}->value;";

        if (\substr($code, 0, 8) === '        ') {
            return \substr($code, 8);
        }

        return $code;
    }

    $nullCheck = function (bool $nullable, $argumentName, string $code) use ($variableName): string {
        if (! $nullable) {
            return "            $code\n";
        }

        return <<<CODE
            && ((null === \$this->$argumentName && null === \${$variableName}->$argumentName)
                || (null !== \$this->$argumentName && null !== \${$variableName}->$argumentName $code)
            )

CODE;
    };

    $namespace = null;
    $name = null;
    foreach ($constructor->arguments() as $argument) {
        $nullableOrScalarType = (null === $argument->type() || $argument->isScalartypeHint());
        if ($nullableOrScalarType && ! $argument->isList()) {
            $addCode .= "            && \$this->{$argument->name()} === \$$variableName->{$argument->name()}\n";
            continue;
        }

        if (! $nullableOrScalarType) {
            $nsPosition = \strrpos($argument->type(), '\\');

            $namespace = \substr($argument->type(), 0, $nsPosition);
            $name = \substr($argument->type(), $nsPosition + 1);

            if ($collection->hasDefinition($namespace, $name)) {
                $definition = $collection->definition($namespace, $name);
            } elseif ($collection->hasConstructorDefinition($argument->type())) {
                $definition = $collection->constructorDefinition($argument->type());
            }
        }

        $argumentName = $argument->name();

        if ($argument->isList()) {
            if ($code === '') {
                $code .= "if (\count(\$this->$argumentName) !== \count(\${$variableName}->{$argumentName})) {\n";
            } else {
                $code .= "        if (\count(\$this->$argumentName) !== \count(\${$variableName}->{$argumentName})) {\n";
            }

            $code .= "            return false;\n";
            $code .= "        }\n\n";
            $code .= "        foreach (\$this->$argumentName as \$__i => \$__value) {\n";
            if ($nullableOrScalarType) {
                $code .= "            if (\${$variableName}->{$argumentName}[\$__i] !== \$__value) {\n";
            } else {
                $compare = false;
                foreach ($definition->derivings() as $deriving) {
                    switch ((string) $deriving) {
                        case Deriving\Equals::VALUE:
                            $compare = "\${$variableName}->{$argumentName}[\$__i]->equals(\$__value)";
                            break;
                        case Deriving\ToArray::VALUE:
                            $compare = "\${$variableName}->{$argumentName}[\$__i]->toArray() === \$__value->toArray()";
                            break;
                        case Deriving\ToScalar::VALUE:
                            $compare = "\${$variableName}->{$argumentName}[\$__i]->toScalar() === \$__value->toScalar()";
                            break;
                        case Deriving\Enum::VALUE:
                        case Deriving\ToString::VALUE:
                        case Deriving\Uuid::VALUE:
                            $compare = "\${$variableName}->{$argumentName}[\$__i]->toString() === \$__value->toString()";
                            break;
                    }
                }

                if (! $compare) {
                    $message = \sprintf('No comparable deriving given for argument $%s', $argumentName);
                    if ($namespace && $name) {
                        $message .= \sprintf(' on definition %s\\%s', $namespace, $name);
                    }

                    throw new \RuntimeException($message);
                }

                $code .= "            if (! $compare) {\n";
            }

            $code .= "                return false;\n            }\n        }\n\n";

            continue;
        }

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\Equals::VALUE:
                case Deriving\Enum::VALUE:
                    $addCode .= $nullCheck($argument->nullable(), $argumentName, "&& \$this->{$argumentName}->equals(\${$variableName}->{$argumentName})");
                    continue 3;
                case Deriving\ToArray::VALUE:
                    $addCode .= $nullCheck($argument->nullable(), $argumentName, "&& \$this->{$argumentName}->toArray() === \${$variableName}->{$argumentName}->toArray()");
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $addCode .= $nullCheck($argument->nullable(), $argumentName, "&& \$this->{$argumentName}->toScalar() === \${$variableName}->{$argumentName}->toScalar()");
                    continue 3;
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    $addCode .= $nullCheck($argument->nullable(), $argumentName, "&& \$this->{$argumentName}->toString() === \${$variableName}->{$argumentName}->toString()");
                    continue 3;
            }
        }

        $addCode .= $nullCheck($argument->nullable(), $argumentName, "&& \$this->{$argumentName} === \${$variableName}->{$argumentName}");
    }

    if ($addCode !== '        return ') {
        if ($isFinal && '' === $code) {
            $addCode = \str_replace('        return             &&', 'return', $addCode);
        } else {
            $addCode = \str_replace('        return             &&', '        return', $addCode);
        }
        $addCode = \substr($addCode, 0, -1) . ';';
        $code .= $addCode;
    }

    return $code;
}
