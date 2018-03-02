<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;

const buildTraits = '\Fpp\Builder\buildTraits';

function buildTraits(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\Query())
        ) {
            return "use \Prooph\Common\Messaging\PayloadTrait;\n";
        }
    }

    return $placeHolder;
}
