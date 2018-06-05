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

const buildScalarConstructorConditions = '\Fpp\Builder\buildScalarConstructorConditions';

function buildScalarConstructorConditions(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor || ! isScalarConstructor($constructor)) {
        return $placeHolder;
    }

    $code = '';

    foreach ($definition->conditions() as $condition) {
        if ('_' === $condition->constructor()
            || false !== \strrpos($constructor->name(), $condition->constructor())
        ) {
            $code .= <<<CODE
        if ({$condition->code()}) {
            throw new \\InvalidArgumentException('{$condition->errorMessage()}');
        }


CODE;
        }
    }

    return empty($code) ? $placeHolder : \substr($code, 8);
}