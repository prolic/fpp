<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;

const buildVariableName = '\Fpp\Builder\buildVariableName';

function buildVariableName(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    return lcfirst($definition->name());
}
