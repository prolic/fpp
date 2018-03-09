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

const buildPayloadValidation = '\Fpp\Builder\buildPayloadValidation';

function buildPayloadValidation(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())) {
            $inclFirstArgument = false;
        }

        if ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\Query())
        ) {
            $inclFirstArgument = true;
        }
    }

    if (! isset($inclFirstArgument)) {
        return $placeHolder;
    }

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
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " || ! is_int(\$payload['{$argument->name()}'])";
            }

            $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_{$argument->type()}(\$payload['{$argument->name()}'])$floatCheck) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a {$argument->type()}");
            }


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && $argument->nullable()) {
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " && ! is_int(\$payload['{$argument->name()}'])";
            }

            $code .= <<<CODE
            if (isset(\$payload['{$argument->name()}']) && ! is_{$argument->type()}(\$payload['{$argument->name()}'])$floatCheck) {
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
                    if ($argument->nullable()) {
                        $code .= <<<CODE
            if (isset(\$payload['{$argument->name()}']) && ! is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array in payload");
            }


CODE;
                    } else {
                        $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
            }


CODE;
                    }
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $type = strtolower($definition->constructors()[0]->name());
                    if ($argument->nullable()) {
                        $code .= <<<CODE
            if (isset(\$payload['{$argument->name()}']) && ! is_{$type}(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$type} in payload");
            }


CODE;
                    } else {
                        $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_{$type}(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a $type");
            }


CODE;
                    }
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    if ($argument->nullable()) {
                        $code .= <<<CODE
            if (isset(\$payload['{$argument->name()}']) && ! is_string(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a string in payload");
            }


CODE;
                    } else {
                        $code .= <<<CODE
            if (! isset(\$payload['{$argument->name()}']) || ! is_string(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a string");
            }


CODE;
                    }
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
