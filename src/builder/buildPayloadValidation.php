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
    $inclFirstArgument = null;

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\MicroAggregateChanged())
        ) {
            $inclFirstArgument = false;
        }

        if ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\Query())
        ) {
            $inclFirstArgument = true;
        }
    }

    if (null === $inclFirstArgument) {
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

        if ($argument->isList() && $argument->isScalartypeHint() && ! $argument->nullable()) {
            $floatCheck = '';
            $startFloatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " && ! \is_int(\$payload['{$argument->name()}']))";
                $startFloatCheck = '(';
            }

            $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || ! \is_array(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
        }

        foreach (\$payload['{$argument->name()}'] as \$__value) {
            if ($startFloatCheck! \is_{$argument->type()}(\$__value)$floatCheck) {
               throw new \InvalidArgumentException("'{$argument->name()}' is not an array of {$argument->type()}");
            }
        }


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && ! $argument->nullable()) {
            $floatCheck = '';
            $startFloatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " && ! \is_int(\$payload['{$argument->name()}']))";
                $startFloatCheck = '(';
            }

            $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || $startFloatCheck! \is_{$argument->type()}(\$payload['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a {$argument->type()}");
        }


CODE;
            continue;
        }

        if ($argument->isList() && $argument->isScalartypeHint() && $argument->nullable()) {
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " && ! \is_int(\$payload['{$argument->name()}'])";
            }

            $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}'])) {
            if (! \is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' must be an array in payload");
            }

            foreach (\$payload['{$argument->name()}'] as \$__value) {
                if (! \is_{$argument->type()}(\$__value)$floatCheck) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array of {$argument->type()} in payload");
                }
            }
        }


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && $argument->nullable()) {
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " && ! \is_int(\$payload['{$argument->name()}'])";
            }

            $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}']) && ! \is_{$argument->type()}(\$payload['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$argument->type()} in payload");
        }


CODE;
            continue;
        }

        $position = \strrpos($argument->type(), '\\');

        $namespace = \substr($argument->type(), 0, $position);
        $name = \substr($argument->type(), $position + 1);

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
                    if ($argument->isList() && $argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}'])) {
            if (! \is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' must be an array in payload");
            }

            foreach (\$payload['{$argument->name()}'] as \$__value) {
                if (! \is_array(\$__value)) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array of arrays in payload");
                }
            }
        }


CODE;
                    } elseif (! $argument->isList() && $argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}']) && ! \is_array(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array in payload");
        }


CODE;
                    } elseif ($argument->isList() && ! $argument->nullable()) {
                        $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || ! \is_array(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
        }

        foreach (\$payload['{$argument->name()}'] as \$__value) {
            if (! \is_array(\$__value)) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is not an array of arrays in payload");
            }
        }


CODE;
                    } else {
                        $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || ! \is_array(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
        }


CODE;
                    }
                    continue 3;
                case Deriving\ToScalar::VALUE:
                    $type = \strtolower($definition->constructors()[0]->name());

                    $floatCheck = '';

                    if ($type === 'float' && $argument->isList()) {
                        $floatCheck = ' && ! \is_int($__value)';
                    } elseif ($type === 'float') {
                        $floatCheck = " && ! \is_int(\$payload['{$argument->name()}'])";
                    }

                    if ($argument->isList() && $argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}'])) {
            if (! \is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' must be an array in payload");
            }

            foreach (\$payload['{$argument->name()}'] as \$__value) {
                if (! \is_{$type}(\$__value)$floatCheck) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array of {$type} in payload");
                }
            }
        }


CODE;
                    } elseif (! $argument->isList() && $argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}']) && ! \is_{$type}(\$payload['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$type} in payload");
        }


CODE;
                    } elseif ($argument->isList() && ! $argument->nullable()) {
                        $startFloatCheck = '';

                        if ($type === 'float') {
                            $floatCheck = ' && ! \is_int($__value))';
                            $startFloatCheck = '(';
                        }

                        $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || ! \is_array(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
        }

        foreach (\$payload['{$argument->name()}'] as \$__value) {
            if ($startFloatCheck! \is_{$type}(\$__value)$floatCheck) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is is not an array of $type");
            }
        }


CODE;
                    } else {
                        $startFloatCheck = '';

                        if ($type === 'float') {
                            $floatCheck = " && ! \is_int(\$payload['{$argument->name()}']))";
                            $startFloatCheck = '(';
                        }

                        $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || $startFloatCheck! \is_{$type}(\$payload['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not a $type");
        }


CODE;
                    }
                    continue 3;
                case Deriving\Enum::VALUE:
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    if ($argument->isList() && $argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}'])) {
            if (! \is_array(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' must be an array in payload");
            }

            foreach (\$payload['{$argument->name()}'] as \$__value) {
                if (! \is_string(\$__value)) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array of string in payload");
                }
            }
        }


CODE;
                    } elseif (! $argument->isList() && $argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$payload['{$argument->name()}']) && ! \is_string(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a string in payload");
        }


CODE;
                    } elseif ($argument->isList() && ! $argument->nullable()) {
                        $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || ! \is_array(\$payload['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload or is not an array");
        }

        foreach (\$payload['{$argument->name()}'] as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is not an array of string in payload");
            }
        }


CODE;
                    } else {
                        $code .= <<<CODE
        if (! isset(\$payload['{$argument->name()}']) || ! \is_string(\$payload['{$argument->name()}'])) {
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

    if (empty($code)) {
        return $placeHolder;
    }

    return \substr($code, 8, -1);
}
