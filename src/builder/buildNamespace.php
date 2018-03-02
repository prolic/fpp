<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use function Fpp\isScalarConstructor;

const buildNamespace = '\Fpp\Builder\buildNamespace';

function buildNamespace(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if ($constructor) {
        if (isScalarConstructor($constructor)) {
            return $definition->namespace();
        }
        $position = strrpos($constructor->name(), '\\');

        if (false === $position) {
            return '';
        }

        return substr($constructor->name(), 0, $position);
    }

    return $definition->namespace();
}
