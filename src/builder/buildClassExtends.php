<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use function Fpp\isScalarConstructor;

const buildClassExtends = '\Fpp\Builder\buildClassExtends';

function buildClassExtends(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())) {
            return ' extends \Prooph\EventSourcing\AggregateChanged';
        }

        if ($deriving->equals(new Deriving\DomainEvent())) {
            return ' extends \Prooph\Common\Messaging\DomainEvent';
        }

        if ($deriving->equals(new Deriving\Command())) {
            return ' extends \Prooph\Common\Messaging\Command';
        }

        if ($deriving->equals(new Deriving\Query())) {
            return ' extends \Prooph\Common\Messaging\Query';
        }

        if ($deriving->equals(new Deriving\MicroAggregateChanged())) {
            return ' extends \Prooph\Common\Messaging\DomainEvent';
        }
    }

    if ($definition->isMarker() && null !== $parentMarker = $definition->parentMarker()) {
        $namespace = $definition->namespace();
        $name = $parentMarker;

        if (false !== strpos($parentMarker, '\\')) {
            $namespace = substr($parentMarker, 0, strrpos($parentMarker, '\\'));
            $name = substr($parentMarker, strrpos($parentMarker, '\\') + 1);
            $parentMarker = '\\' . $parentMarker;
        }

        if (!$collection->hasDefinition($namespace, $name)) {
            throw new \RuntimeException(sprintf(
                'Marker %s\\%s cannot extend unknown marker %s\\%s',
                $definition->namespace(),
                $definition->name(),
                $namespace,
                $name
            ));
        }

        $parentDefinition = $collection->definition($namespace, $name);
        if (!$parentDefinition->isMarker()) {
            throw new \RuntimeException(sprintf(
                'Marker %s\\%s cannot extend %s\\%s because it\'s not a marker',
                $definition->namespace(),
                $definition->name(),
                $namespace,
                $name
            ));
        }

        return sprintf(' extends %s', $parentMarker);
    }

    $fullQualifiedDefinitionClassName = $definition->name();

    if ($definition->namespace()) {
        $fullQualifiedDefinitionClassName = $definition->namespace() . '\\' . $fullQualifiedDefinitionClassName;
    }

    if ($constructor) {
        if (isScalarConstructor($constructor)) {
            $namespace = $definition->namespace();
        } else {
            $position = \strrpos($constructor->name(), '\\');
            $namespace = \substr($constructor->name(), 0, $position);
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
