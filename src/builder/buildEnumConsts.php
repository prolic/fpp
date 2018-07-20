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
use function Fpp\var_export as fpp_var_export;

const buildEnumConsts = '\Fpp\Builder\buildEnumConsts';

function buildEnumConsts(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    $replace = '';

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

    foreach ($definition->constructors() as $key => $constructor2) {
        $class = buildReferencedClass($definition->namespace(), $constructor2->name());
        $export = empty($enumDeriving->valueMapping()) ? $key : fpp_var_export($enumDeriving->valueMapping()[$class], '    ');
        $replace .= "    public const $class = $export;\n";
    }

    return \substr($replace, 4, -1);
}
