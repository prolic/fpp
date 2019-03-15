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

use function Fpp\buildReferencedClass;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving\Enum;
use function Fpp\var_export as fpp_var_export;

const buildEnumOptions = '\Fpp\Builder\buildEnumOptions';

function buildEnumOptions(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if ($constructor || $definition->isMarker()) {
        return $placeHolder;
    }

    $namespace = buildNamespace($definition, $constructor, $collection, 'namespace');

    $found = false;
    $enumDeriving = new Enum();
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals($enumDeriving)) {
            /** @var Enum */
            $enumDeriving = $deriving;
            $found = true;
            break;
        }
    }
    if (! $found) {
        return $placeHolder;
    }

    $valueMapping = $enumDeriving->valueMapping();

    $replace = '';
    foreach ($definition->constructors() as $key => $definitionConstructor) {
        $class = buildReferencedClass($namespace, $definitionConstructor->name());

        $keyValue = $enumDeriving->useValue() ? $class : $key;
        $value = empty($valueMapping) ? $keyValue : $valueMapping[$class];
        $value = fpp_var_export($value, '        ');
        $replace .= "        '$class' => $value,\n";
    }

    return \substr($replace, 8, -1);
}
