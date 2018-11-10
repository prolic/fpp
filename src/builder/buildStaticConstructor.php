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

use function Fpp\buildDocBlockArgumentTypes;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;

const buildStaticConstructor = '\Fpp\Builder\buildStaticConstructor';

function buildStaticConstructor(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor || \count($constructor->arguments()) === 0) {
        return $placeHolder;
    }

    return buildDocBlockArgumentTypes($constructor->arguments()) . <<<CODE
public static function with({{arguments}}): {{class_name}}
    {
        {{static_constructor_body}}
    }

CODE;
}
