<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use function Fpp\buildReferencedClass;

const buildEnumOptions = '\Fpp\Builder\buildEnumOptions';

function buildEnumOptions(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if ($constructor) {
        return $placeHolder;
    }

    $namespace = buildNamespace($definition, $constructor, $collection, 'namespace');

    $replace = '';
    foreach ($definition->constructors() as $definitionConstructor) {
        $class = buildReferencedClass($namespace, $definitionConstructor->name());
        $replace .= "            $class::VALUE => $class::class,\n";
    }

    return substr($replace, 12, -1);
}
