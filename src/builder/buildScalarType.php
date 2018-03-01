<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use function Fpp\isScalarConstructor;

const buildScalarType = '\Fpp\Builder\buildScalarType';

function buildScalarType(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    if (isScalarConstructor($constructor)) {
        return strtolower($constructor->name());
    }

    if (empty($constructor->arguments())) {
        return $placeHolder;
    }

    $argument = $constructor->arguments()[0];

    return strtolower($argument->type());
}
