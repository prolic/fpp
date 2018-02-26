<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use function Fpp\isScalarConstructor;

const buildClassName = '\Fpp\Builder\buildClassName';

function buildClassName(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection): string
{
    if ($constructor && ! isScalarConstructor($constructor)) {
        $position = strrpos($constructor->name(), '\\');

        if (false === $position) {
            return $constructor->name();
        }

        return substr($constructor->name(), $position + 1);
    }

    return  $definition->name();
}
