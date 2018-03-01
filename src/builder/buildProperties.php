<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;

const buildProperties = '\Fpp\Builder\buildProperties';

function buildProperties(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\Query())
        ) {
            return $placeHolder;
        }
    }

    $properties = '';

    foreach ($constructor->arguments() as $argument) {
        $properties .= '        private $' . $argument->name() . ";\n";
    }

    return ltrim($properties);
}
