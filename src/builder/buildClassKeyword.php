<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;

const buildClassKeyword = '\Fpp\Builder\buildClassKeyword';

function buildClassKeyword(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return 'abstract ';
    }

    $constructors = $definition->constructors();

    if (1 === count($constructors)) {
        return 'final ';
    }

    if ($definition->namespace()) {
        $name = str_replace($definition->namespace() . '\\', '', $constructor->name());
    } else {
        $name = $constructor->name();
    }

    if ($definition->name() === $name) {
        return '';
    }

    return 'final ';
}
