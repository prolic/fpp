<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use function Fpp\isScalarConstructor;

const buildScalarType = '\Fpp\Builder\buildScalarType';

function buildScalarType(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $valid = false;

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\FromScalar())
            || $deriving->equals(new Deriving\ToScalar())
        ) {
            $valid = true;
            break;
        }
    }

    if (! $valid) {
        return $placeHolder;
    }

    if (isScalarConstructor($constructor)) {
        return strtolower($constructor->name());
    }

    $argument = $constructor->arguments()[0];

    return strtolower($argument->type());
}
