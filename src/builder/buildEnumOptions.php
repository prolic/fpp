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
        $value = var_export54($value, '        ');
        $replace .= "        '$class' => $value,\n";
    }

    return substr($replace, 8, -1);
}

// found on https://stackoverflow.com/questions/24316347/how-to-format-var-export-to-php5-4-array-syntax
function var_export54($var, $indent = '')
{
    switch (gettype($var)) {
        case 'string':
            return '\'' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '\'';
        case 'array':
            $indexed = array_keys($var) === range(0, count($var) - 1);
            $r = [];
            foreach ($var as $key => $value) {
                $r[] = "$indent    "
                    . ($indexed ? '' : var_export54($key) . ' => ')
                    . var_export54($value, "$indent    ");
            }

            return "[\n" . implode(",\n", $r) . ",\n" . $indent . ']';
        case 'boolean':
            return $var ? 'true' : 'false';
        default:
            return var_export($var, true);
    }
}
