<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use function Fpp\buildReferencedClass;

const buildEnumValue = '\Fpp\Builder\buildEnumValue';

function buildEnumValue(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $namespace = buildNamespace($definition, $constructor, $collection, 'namespace');

    return buildReferencedClass($namespace, $constructor->name());
}
