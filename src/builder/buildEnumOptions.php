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
use Fpp\Deriving\Enum;
use function Fpp\buildReferencedClass;
use function Fpp\var_export;

const buildEnumOptions = '\Fpp\Builder\buildEnumOptions';

function buildEnumOptions(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if ($constructor) {
        return $placeHolder;
    }

    $namespace = buildNamespace($definition, $constructor, $collection, 'namespace');

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Enum())) {
            break;
        }
    }

    /* @var Enum $deriving */
    $valueMapping = $deriving->valueMapping();

    $replace = '';
    foreach ($definition->constructors() as $key => $definitionConstructor) {
        $class = buildReferencedClass($namespace, $definitionConstructor->name());
        $value = empty($valueMapping) ? $key : $valueMapping[$class];
        $value = var_export($value, '        ');
        $replace .= "        '$class' => $value,\n";
    }

    return \substr($replace, 8, -1);
}
