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

const buildArguments = '\Fpp\Builder\buildArguments';

function buildArguments(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $argumentList = '';

    foreach ($constructor->arguments() as $argument) {
        if (null === $argument->type()) {
            $argumentList .= '$' . $argument->name() . ', ';
            continue;
        }

        if ($argument->nullable()) {
            $argumentList .= '?';
        }

        if (1 === \count($constructor->arguments()) && $argument->isScalartypeHint()) {
            $argumentType = $argument->isList() ? $argument->type() . ' ...' : $argument->type() . ' ';
            $argumentList .= $argumentType . '$' . $argument->name() . ', ';
            continue;
        } elseif ($argument->isScalartypeHint()) {
            $argumentType = $argument->isList() ? 'array' : $argument->type();
            $argumentList .= $argumentType . ' $' . $argument->name() . ', ';
            continue;
        }

        $nsPosition = \strrpos($argument->type(), '\\');

        $namespace = \substr($argument->type(), 0, $nsPosition);
        $name = \substr($argument->type(), $nsPosition + 1);

        $type = $namespace === $definition->namespace()
            ? $name
            : '\\' . $argument->type();

        if (1 === \count($constructor->arguments()) && $argument->isList()) {
            $argumentList .= $type . ' ...$' . $argument->name() . ', ';
        } elseif ($argument->isList()) {
            $argumentList .= 'array $' . $argument->name() . ', ';
        } else {
            $argumentList .= $type . ' $' . $argument->name() . ', ';
        }
    }

    if ('' === $argumentList) {
        return $placeHolder;
    }

    return \substr($argumentList, 0, -2);
}
