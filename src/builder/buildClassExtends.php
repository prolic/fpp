<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use function Fpp\isScalarConstructor;

const buildClassExtends = '\Fpp\Builder\buildClassExtends';

function buildClassExtends(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection): string
{
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\DomainEvent())
        ) {
            return ' extends \Prooph\Common\Messaging\DomainEvent';
        }

        if ($deriving->equals(new Deriving\Command())) {
            return ' extends \Prooph\Common\Messaging\Command';
        }

        if ($deriving->equals(new Deriving\Query())) {
            return ' extends \Prooph\Common\Messaging\Query';
        }
    }

    $fullQualifiedDefinitionClassName = $definition->name();

    if ($definition->namespace()) {
        $fullQualifiedDefinitionClassName = $definition->namespace() . '\\' . $fullQualifiedDefinitionClassName;
    }

    if ($constructor) {
        if (isScalarConstructor($constructor)) {
            $namespace = $definition->namespace();
        } else {
            $position = strrpos($constructor->name(), '\\');

            if (false === $position) {
                $namespace = '';
            } else {
                $namespace = substr($constructor->name(), 0, $position);
            }
        }
    } else {
        $namespace = $definition->namespace();
    }

    if ($constructor && ! isScalarConstructor($constructor) && $constructor->name() !== $fullQualifiedDefinitionClassName) {
        if ($namespace === $definition->namespace()) {
            $baseClass = $definition->name();
        } else {
            $baseClass = '\\' . $fullQualifiedDefinitionClassName;
        }

        return ' extends ' . $baseClass;
    }

    return '';
}
