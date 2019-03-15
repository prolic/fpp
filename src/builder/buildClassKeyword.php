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
use Fpp\Deriving;

const buildClassKeyword = '\Fpp\Builder\buildClassKeyword';

function buildClassKeyword(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    $enum = new Deriving\Enum();

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals($enum)) {
            return 'final ';
        }
    }

    if (null === $constructor) {
        return 'abstract ';
    }

    $constructors = $definition->constructors();

    if (1 === \count($constructors)) {
        return 'final ';
    }

    $name = \str_replace($definition->namespace() . '\\', '', $constructor->name());

    if ($definition->name() === $name) {
        return '';
    }

    return 'final ';
}
