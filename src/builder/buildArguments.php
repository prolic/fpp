<?php

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

        if ($argument->isScalartypeHint()) {
            $argumentList .= $argument->type() . ' $' . $argument->name() . ', ';
            continue;
        }

        $nsPosition = strrpos($argument->type(), '\\');

        if (false !== $nsPosition) {
            $namespace = substr($argument->type(), 0, $nsPosition);
            $name = substr($argument->type(), $nsPosition + 1);
        } else {
            $namespace = '';
            $name = $argument->type();
        }

        $type = $namespace === $definition->namespace()
            ? $name
            : '\\' . $argument->type();

        $argumentList .= $type . ' $' . $argument->name() . ', ';
    }

    if ('' === $argumentList) {
        return $placeHolder;
    }

    return substr($argumentList, 0, -2);
}
