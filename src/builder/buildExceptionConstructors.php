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

const buildExceptionConstructors = '\Fpp\Builder\buildExceptionConstructors';

function buildExceptionConstructors(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $supported = false;
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\Exception())) {
            $supported = true;
            break;
        }
    }
    if (! $supported) {
        return $placeHolder;
    }

    $code = '';

    foreach ($deriving->constructors() as $ctor) {
        $fnArgs = \implode(', ', \array_merge(
            \array_map(function (Argument $arg): string {
                return \sprintf('%s $%s', $arg->type(), $arg->name());
            }, $ctor->arguments()),
            [
                'int $code = 0',
                '\Exception $previous = null',
            ]
        ));

        $selfArgs = \implode(', ', \array_merge(
            \array_map(
                function (Argument $arg): string {
                    return \sprintf('$%s', $arg->name());
                },
                \array_filter(
                    $ctor->arguments(),
                    function (Argument $arg) use ($definition): bool {
                        foreach ($definition->constructors()[0]->arguments() as $ctorArg) {
                            if ($arg == $ctorArg) {
                                return true;
                            }
                        }

                        return false;
                    }
                )
            ),
            [
                \sprintf('sprintf(\'%s\')', $ctor->message()),
                '$code',
                '$previous',
            ]
        ));

        $code .= "\n    public static function {$ctor->name()}($fnArgs): self\n";
        $code .= "    {\n";
        $code .= "        return new self($selfArgs);\n";
        $code .= '    }';
    }

    return $code;
}
