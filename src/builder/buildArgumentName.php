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
use function Fpp\isScalarConstructor;

const buildArgumentName = '\Fpp\Builder\buildArgumentName';

function buildArgumentName(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    if (isScalarConstructor($constructor)) {
        return \lcfirst($definition->name());
    }

    if (1 !== \count($constructor->arguments())) {
        return $placeHolder;
    }

    $argument = $constructor->arguments()[0];

    return \lcfirst($argument->name());
}
