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

const buildToArrayBody = '\Fpp\Builder\buildToArrayBody';

function buildToArrayBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $valid = false;

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\ToArray())) {
            $valid = true;
            break;
        }
    }

    if (! $valid) {
        return $placeHolder;
    }

    $prefixCode = '';

    $code = "return [\n";

    $class = $definition->namespace();

    if ('' !== $class) {
        $class .= '\\';
    }

    $class .= $definition->name();

    foreach ($constructor->arguments() as $key => $argument) {
        if ($argument->nullable() && $argument->isScalartypeHint()) {
            $code .= "            '{$argument->name()}' => ";
            $code .= "null === \$this->{$argument->name()} ? null : \$this->{$argument->name()},\n";
            continue;
        }

        if ($argument->isScalartypeHint() && ! $argument->isList()) {
            $code .= "            '{$argument->name()}' => ";
            $code .= "\$this->{$argument->name()},\n";
            continue;
        }

        if ($argument->isList()) {
            $argumentName = $argument->name();
            if (null !== $argument->type() && ! $argument->isScalartypeHint()) {
                $position = strrpos($argument->type(), '\\');

                $namespace = substr($argument->type(), 0, $position);
                $name = substr($argument->type(), $position + 1);

                if ($collection->hasDefinition($namespace, $name)) {
                    $argumentDefinition = $collection->definition($namespace, $name);
                } elseif ($collection->hasConstructorDefinition($argument->type())) {
                    $argumentDefinition = $collection->constructorDefinition($argument->type());
                } else {
                    throw new \RuntimeException("Cannot build ToArray for $class, no argument type hint for {$argument->type()} given");
                }

                $prefixCode .= "        \${$argumentName} = [];\n\n";

                $prefixCode .= "        foreach (\$this->$argumentName as \$__value) {\n";
                foreach ($argumentDefinition->derivings() as $deriving) {
                    $match = false;
                    switch ((string) $deriving) {
                        case Deriving\ToArray::VALUE:
                            $prefixCode .= "            \${$argumentName}[] = \$__value->toArray();\n";
                            $match = true;
                            break;
                        case Deriving\ToScalar::VALUE:
                            $prefixCode .= "            \${$argumentName}[] = \$__value->toScalar();\n";
                            $match = true;
                            break;
                        case Deriving\Enum::VALUE:
                            $prefixCode .= "            \${$argumentName}[] = \$__value->name();\n";
                            $match = true;
                            break;
                        case Deriving\ToString::VALUE:
                        case Deriving\Uuid::VALUE:
                            $prefixCode .= "            \${$argumentName}[] = \$__value->toString();\n";
                            $match = true;
                            break;
                    }
                }

                if (! $match) {
                    throw new \RuntimeException(sprintf(
                        'No toArray, ToScalar, ToString, Enum or Uuid deriving given for argument $%s on definition %s',
                        $argumentName,
                        $namespace . '\\' . $name
                    ));
                }

                $prefixCode .= "        }\n\n";
                $code .= "            '{$argumentName}' => \${$argumentName},\n";
            } else {
                $code .= "            '{$argumentName}' => \$this->{$argumentName},\n";
            }

            continue;
        }

        $code .= "            '{$argument->name()}' => ";

        $position = strrpos($argument->type(), '\\');

        $namespace = substr($argument->type(), 0, $position);
        $name = substr($argument->type(), $position + 1);

        if ($collection->hasDefinition($namespace, $name)) {
            $argumentDefinition = $collection->definition($namespace, $name);
        } elseif ($collection->hasConstructorDefinition($argument->type())) {
            $argumentDefinition = $collection->constructorDefinition($argument->type());
        } else {
            throw new \RuntimeException("Cannot build ToArray for $class, no argument type hint for {$argument->type()} given");
        }

        foreach ($argumentDefinition->derivings() as $deriving) {
            if ($argument->nullable()) {
                $code .= "null === \$this->{$argument->name()} ? null : ";
            }

            switch ((string) $deriving) {
                case Deriving\Enum::VALUE:
                    $code .= "\$this->{$argument->name()}->name(),\n";
                    continue 3;
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

    $code .= "        ];\n";

    if (! empty($prefixCode)) {
        $prefixCode = substr($prefixCode, 8) . '        ';
    }

    return $prefixCode . $code;
}
