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

const buildFromArrayBody = '\Fpp\Builder\buildFromArrayBody';

function buildFromArrayBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $position = \strrpos($constructor->name(), '\\');

    if (false !== $position) {
        $constructorNamespace = \substr($constructor->name(), 0, $position);
    } else {
        $constructorNamespace = '';
    }

    $code = '';
    $arguments = [];
    foreach ($constructor->arguments() as $key => $argument) {
        $arguments[] = '$' . $argument->name();

        if (null === $argument->type()) {
            $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array");
        }

        \${$argument->name()} = \$data['{$argument->name()}'];


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && ! $argument->nullable() && ! $argument->isList()) {
            $floatCheckStart = '';
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheckStart = '(';
                $floatCheck = " && ! \is_int(\$data['{$argument->name()}']))";
            }

            $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || {$floatCheckStart}! \is_{$argument->type()}(\$data['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a {$argument->type()}");
        }

        \${$argument->name()} = \$data['{$argument->name()}'];


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && $argument->isList()) {
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = ' && ! \is_int($__value)';
            }

            $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }
        
        \${$argument->name()} = [];

        foreach (\$data['{$argument->name()}'] as \$__value) {
            if (! \is_{$argument->type()}(\$__value)$floatCheck) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' in data array is not an array of {$argument->type()}");
            }

            \${$argument->name()}[] = \$__value;
        }


CODE;
            continue;
        }

        if ($argument->isScalartypeHint() && $argument->nullable()) {
            $floatCheck = '';

            if ($argument->type() === 'float') {
                $floatCheck = " && ! \is_int(\$data['{$argument->name()}'])";
            }

            $code .= <<<CODE
        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_{$argument->type()}(\$data['{$argument->name()}'])$floatCheck) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$argument->type()} in data array");
            }

            \${$argument->name()} = \$data['{$argument->name()}'];
        } else {
            \${$argument->name()} = null;
        }


CODE;
            continue;
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
            throw new \RuntimeException("Cannot build fromArray for $class , unknown argument {$argument->type()} given");
        }

        $argumentConstructor = $argumentDefinition->constructors()[0];

        if ($constructorNamespace === $namespace) {
            $argumentClass = $name;
        } else {
            $argumentClass = '\\' . $namespace . '\\' . $name;
        }

        foreach ($argumentDefinition->derivings() as $deriving) {
            switch (true) {
                case $deriving instanceof Deriving\FromArray:
                    if ($argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_array(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not an array in data array");
            }

            \${$argument->name()} = $argumentClass::fromArray(\$data['{$argument->name()}']);
        } else {
            \${$argument->name()} = null;
        }


CODE;
                    } elseif ($argument->isList()) {
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }

        \${$argument->name()} = [];

        foreach (\$data['{$argument->name()}'] as \$__value) {
            if (! \is_array(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' in data array or is not an array of arrays");
            }

            \${$argument->name()}[] = $argumentClass::fromArray(\$__value);
        }


CODE;
                    } else {
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }

        \${$argument->name()} = $argumentClass::fromArray(\$data['{$argument->name()}']);


CODE;
                    }
                    continue 3;
                case $deriving instanceof Deriving\FromScalar:
                    if (isScalarConstructor($argumentConstructor)) {
                        $argumentType = \strtolower($argumentConstructor->name());
                    } elseif (isset($argumentConstructor->arguments()[0])) {
                        $argumentType = $argumentConstructor->arguments()[0]->type();
                    } else {
                        throw new \RuntimeException("Cannot build fromArray for $class , unknown argument {$argument->type()} given");
                    }

                    $floatCheck = '';

                    if ($argumentType === 'float') {
                        $floatCheck = " && ! \is_int(\$data['{$argument->name()}'])";
                    }

                    if ($argument->nullable()) {
                        $code .= <<<CODE
        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_{$argumentType}(\$data['{$argument->name()}'])$floatCheck) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a $argumentType in data array");
            }

            \${$argument->name()} = $argumentClass::fromScalar(\$data['{$argument->name()}']);
        } else {
            \${$argument->name()} = null;
        }


CODE;
                    } elseif ($argument->isList()) {
                        if ($argumentType === 'float') {
                            $floatCheck = ' && ! \is_int($__value)';
                        }
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }

        \${$argument->name()} = [];

        foreach (\$data['{$argument->name()}'] as \$__value) {
            if (! \is_$argumentType(\$data['{$argument->name()}'])$floatCheck) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' in data array is not an array of {$argument->type()}");
            }

            \${$argument->name()}[] = $argumentClass::fromScalar(\$__value);
        }


CODE;
                    } else {
                        if (empty($floatCheck)) {
                            $floatCheckStart = '';
                        } else {
                            $floatCheckStart = '(';
                            $floatCheck .= ')';
                        }
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || {$floatCheckStart}! \is_$argumentType(\$data['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a $argumentType");
        }

        \${$argument->name()} = $argumentClass::fromScalar(\$data['{$argument->name()}']);


CODE;
                    }
                    continue 3;
                case $deriving instanceof Deriving\Enum:
                    $fromWhat = $deriving->useValue() ? 'fromValue' : 'fromName';

                    if ($argument->nullable() && $argument->isList()) {
                        $code .= <<<CODE
        \${$argument->name()} = null;

        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_array(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a string");
            }
            
            foreach (\$data['{$argument->name()}'] as \$__value) {
                if (! \is_string(\$__value)) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' in data array is not an array of string");
                }
    
                \${$argument->name()}[] = $argumentClass::{$fromWhat}(\$__value);
            }
        }


CODE;
                    } elseif ($argument->nullable() && ! $argument->isList()) {
                        $code .= <<<CODE
        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_string(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a string in data array");
            }

            \${$argument->name()} = $argumentClass::{$fromWhat}(\$data['{$argument->name()}']);
        } else {
            \${$argument->name()} = null;
        }


CODE;
                    } elseif ($argument->isList() && ! $argument->nullable()) {
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }

        \${$argument->name()} = [];

        foreach (\$data['{$argument->name()}'] as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' in data array is not an array of string");
            }

            \${$argument->name()}[] = $argumentClass::{$fromWhat}(\$__value);
        }


CODE;
                    } else {
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_string(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a string");
        }

        \${$argument->name()} = $argumentClass::{$fromWhat}(\$data['{$argument->name()}']);


CODE;
                    }
                    continue 3;
                case $deriving instanceof Deriving\FromString:
                case $deriving instanceof Deriving\Uuid:
                    if ($argument->nullable() && $argument->isList()) {
                        $code .= <<<CODE
        \${$argument->name()} = null;

        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_array(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a string");
            }
            
            foreach (\$data['{$argument->name()}'] as \$__value) {
                if (! \is_string(\$__value)) {
                    throw new \InvalidArgumentException("Value for '{$argument->name()}' in data array is not an array of string");
                }
    
                \${$argument->name()}[] = $argumentClass::fromString(\$__value);
            }
        }


CODE;
                    } elseif ($argument->nullable() && ! $argument->isList()) {
                        $code .= <<<CODE
        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_string(\$data['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a string in data array");
            }

            \${$argument->name()} = $argumentClass::fromString(\$data['{$argument->name()}']);
        } else {
            \${$argument->name()} = null;
        }


CODE;
                    } elseif ($argument->isList() && ! $argument->nullable()) {
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }

        \${$argument->name()} = [];

        foreach (\$data['{$argument->name()}'] as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' in data array is not an array of string");
            }

            \${$argument->name()}[] = $argumentClass::fromString(\$__value);
        }


CODE;
                    } else {
                        $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_string(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a string");
        }

        \${$argument->name()} = $argumentClass::fromString(\$data['{$argument->name()}']);


CODE;
                    }
                    continue 3;
            }
        }

        if (isScalarConstructor($argumentConstructor)) {
            $argumentType = \strtolower($argumentConstructor->name());
        } elseif (isset($argumentConstructor->arguments()[0])) {
            $argumentType = $argumentConstructor->arguments()[0]->type();
        } else {
            throw new \RuntimeException("Cannot build fromArray for $class , unknown argument {$argument->type()} given");
        }

        if ($argument->nullable()) {
            $floatCheck = '';

            if ($argumentType === 'float') {
                $floatCheck = " && ! \is_int(\$data['{$argument->name()}'])";
            }

            $code .= <<<CODE
        if (isset(\$data['{$argument->name()}'])) {
            if (! \is_{$argumentType}(\$data['{$argument->name()}'])$floatCheck) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a $argumentType in data array");
            }

            \${$argument->name()} = new $argumentClass(\$data['{$argument->name()}']);
        } else {
            \${$argument->name()} = null;
        }


CODE;
        } elseif ($argument->isList()) {
            $floatCheck = '';

            if ($argumentType === 'float') {
                $floatCheck = ' && ! \is_int($__value)';
            }

            $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || ! \is_array(\$data['{$argument->name()}'])) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not an array");
        }
        
        \${$argument->name()} = [];

        foreach (\$data['{$argument->name()}'] as \$__value) {
            if (! \is_{$argument->type()}(\$__value)$floatCheck) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' in data array or is not an array of {$argument->type()}");
            }

            \${$argument->name()}[] = \$__value;
        }


CODE;
        } else {
            $floatCheckStart = '';
            $floatCheck = '';

            if ($argumentType === 'float') {
                $floatCheckStart = '(';
                $floatCheck = ' && ! \is_int($__value))';
            }

            $code .= <<<CODE
        if (! isset(\$data['{$argument->name()}']) || {$floatCheckStart}! \is_{$argumentType}(\$data['{$argument->name()}'])$floatCheck) {
            throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in data array or is not a $argumentType");
        }

        \${$argument->name()} = new $argumentClass(\$data['{$argument->name()}']);


CODE;
        }
    }

    if (\count($arguments) > 2) {
        $arguments = "\n            " . \implode(",\n            ", $arguments) . "\n        ";
    } elseif (\count($arguments) === 1 && $constructor->arguments()[0]->isList()) {
        $arguments = '...' . \implode(', ', $arguments);
    } else {
        $arguments = \implode(', ', $arguments);
    }

    $code .= "        return new self($arguments);\n";

    return \ltrim($code);
}
