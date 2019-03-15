<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;

const buildClassImplements = '\Fpp\Builder\buildClassImplements';

function buildClassImplements(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    $interfaces = [];
    foreach (\array_map('\strval', $definition->markers()) as $marker) {
        if (0 !== \strpos($marker, '\\')) {
            $marker = \sprintf('\\%s\\%s', $definition->namespace(), $marker);
        }

        if (\interface_exists($marker, true)) {
            if (0 !== \count((new \ReflectionClass($marker))->getMethods())) {
                throw new \RuntimeException(\sprintf(
                    'Cannot mark %s\\%s with non marker interface %s',
                    $definition->namespace(),
                    $definition->name(),
                    $marker
                ));
            }

            $interfaces[] = $marker;
            continue;
        }

        $namespace = \ltrim(\substr($marker, 0, \strrpos($marker, '\\')), '\\');
        $name = \substr($marker, \strrpos($marker, '\\') + 1);

        if (! $collection->hasDefinition($namespace, $name)) {
            throw new \RuntimeException(\sprintf(
                'Cannot mark data %s\\%s with unknown marker %s\\%s',
                $definition->namespace(),
                $definition->name(),
                $namespace,
                $name
            ));
        }

        $markerDefinition = $collection->definition($namespace, $name);
        if (! $markerDefinition->isMarker()) {
            throw new \RuntimeException(\sprintf(
                'Cannot mark %s\\%s with data %s\\%s',
                $definition->namespace(),
                $definition->name(),
                $namespace,
                $name
            ));
        }

        if ($definition->namespace() === $markerDefinition->namespace()) {
            $marker = $markerDefinition->name();
        }

        $interfaces[] = $marker;
    }

    if (0 === \count($interfaces)) {
        return '';
    }

    return \sprintf(' implements %s', \implode(', ', $interfaces));
}
