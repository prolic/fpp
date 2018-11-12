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
    $deriving = null;
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

    /** @var Deriving\Exception $deriving */
    $deriving = $deriving;
    foreach ($deriving->constructors() as $ctor) {
        $fnArgs = \implode(', ', \array_merge(
            \array_map(
                function (Argument $arg): string {
                    return \sprintf('%s $%s', $arg->type(), $arg->name());
                },
                \array_unique(
                    \array_merge(
                        $ctor->arguments(),
                        $definition->constructors()[0]->arguments()
                    )
                )
            ),
            [
                'int $code = 0',
                '\Exception $previous = null',
            ]
        ));

        $messageArg = \sprintf('\'%s\'', $ctor->message());
        if (\preg_match_all('/\{{2}([^}]+)\}{2}/', $messageArg, $matches) > 0) {
            $messageArg = \sprintf(
                'sprintf(\'%s\', %s)',
                \preg_replace('/\{{2}([^}]+)\}{2}/', '%s', \substr($messageArg, 1, -1)),
                \implode(', ', \array_map('trim', $matches[1]))
            );
        }
        $selfArgs = \implode(', ', \array_merge(
            \array_map(
                function (Argument $arg): string {
                    return \sprintf('$%s', $arg->name());
                },
                $definition->constructors()[0]->arguments()
            ),
            [
                $messageArg,
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
