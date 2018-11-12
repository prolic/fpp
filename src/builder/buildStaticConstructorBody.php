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

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;

const buildStaticConstructorBody = '\Fpp\Builder\buildStaticConstructorBody';

function buildStaticConstructorBody(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

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

    $foundList = false;

    $start = '';
    $code = '';

    $addArgument = function (int $key, string $name, string $value) use ($inclFirstArgument): string {
        if (false === $inclFirstArgument && 0 === $key) {
            return "$value, [\n";
        }

        return "            '{$name}' => {$value},\n";
    };

    $buildToArrayBlock = function (Argument $argument, string &$start): void {
        $intend = '';
        if (empty($start) && $argument->nullable()) {
            $start = "if (null !== \${$argument->name()}) {\n";
            $intend = '    ';
        }

        if (empty($start)) {
            $start = "\$__array_{$argument->name()} = [];\n\n";
        } else {
            $start .= "$intend        \$__array_{$argument->name()} = [];\n\n";
        }

        $start .= <<<CODE
$intend        foreach (\${$argument->name()} as \$__value) {
$intend            \$__array_{$argument->name()}[] = \$__value->toArray();
$intend        }

CODE;
        if ($argument->nullable()) {
            $start .= <<<CODE
        } else {
            \$__array_{$argument->name()} = null;
        }


CODE;
        } else {
            $start .= "\n";
        }
    };

    foreach ($constructor->arguments() as $key => $argument) {
        if ($argument->isScalartypeHint() || null === $argument->type()) {
            $code .= $addArgument($key, $argument->name(), "\${$argument->name()}");
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
            $code .= $addArgument($key, $argument->name(), "\${$argument->name()}");
            continue;
        }

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\ToArray::VALUE:
                    if ($argument->isList()) {
                        $foundList = true;
                        $buildToArrayBlock($argument, $start);
                        $code .= $addArgument($key, $argument->name(), "\$__array_{$argument->name()}");
                    } else {
                        $value = $argument->nullable()
                            ? "null === \${$argument->name()} ? null : \${$argument->name()}->toArray()"
                            : "\${$argument->name()}->toArray()";

                        $code .= $addArgument($key, $argument->name(), $value);
                    }

                    continue 3;
                case Deriving\ToScalar::VALUE:
                    if ($argument->isList()) {
                        $foundList = true;
                        $buildToArrayBlock($argument, $start);
                        $code .= $addArgument($key, $argument->name(), "\$__array_{$argument->name()}");
                    } else {
                        $value = $argument->nullable()
                            ? "null === \${$argument->name()} ? null : \${$argument->name()}->toScalar()"
                            : "\${$argument->name()}->toScalar()";
                        $code .= $addArgument($key, $argument->name(), $value);
                    }

                    continue 3;
                case Deriving\Enum::VALUE:
                    if ($argument->isList()) {
                        $foundList = true;
                        $buildToArrayBlock($argument, $start);
                        $code .= $addArgument($key, $argument->name(), "\$__array_{$argument->name()}");
                    } else {
                        $asWhat = $deriving->useValue() ? 'value' : 'name';
                        $value = $argument->nullable()
                            ? "null === \${$argument->name()} ? null : \${$argument->name()}->{$asWhat}()"
                            : "\${$argument->name()}->{$asWhat}()";
                        $code .= $addArgument($key, $argument->name(), $value);
                    }

                    continue 3;
                case Deriving\ToString::VALUE:
                case Deriving\Uuid::VALUE:
                    if ($argument->isList()) {
                        $foundList = true;
                        $buildToArrayBlock($argument, $start);
                        $code .= $addArgument($key, $argument->name(), "\$__array_{$argument->name()}");
                    } else {
                        $value = $argument->nullable()
                            ? "null === \${$argument->name()} ? null : \${$argument->name()}->toString()"
                            : "\${$argument->name()}->toString()";
                        $code .= $addArgument($key, $argument->name(), $value);
                    }

                    continue 3;
            }
        }

        $code .= $addArgument($key, $argument->name(), "\${$argument->name()}");
    }

    if (! $foundList) {
        $start = 'return new self(';
    } else {
        $start .= '        return new self(';
    }

    if ($inclFirstArgument) {
        $start .= "[\n            ";
    }

    return $start . \ltrim($code) . '        ]);';
}
