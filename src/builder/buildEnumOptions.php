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
use function Fpp\buildReferencedClass;

const buildEnumOptions = '\Fpp\Builder\buildEnumOptions';

function buildEnumOptions(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if ($constructor) {
        return $placeHolder;
    }

    $namespace = buildNamespace($definition, $constructor, $collection, 'namespace');

    $replace = '';
    foreach ($definition->constructors() as $definitionConstructor) {
        $class = buildReferencedClass($namespace, $definitionConstructor->name());
        $replace .= "        $class::VALUE => $class::class,\n";
    }

    return substr($replace, 8, -1);
}
