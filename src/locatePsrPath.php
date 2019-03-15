<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

const locatePsrPath = '\Fpp\locatePsrPath';

function locatePsrPath(array $prefixesPsr4, array $prefixesPsr0, Definition $definition, ?Constructor $constructor): string
{
    if ($constructor && ! \in_array(
            $constructor->name(),
            ['Bool', 'Bool[]', 'Float', 'Float[]', 'Int', 'Int[]', 'String', 'String[]'],
            true
    )) {
        $class = $constructor->name();
    } else {
        $class = $definition->namespace() . '\\' . $definition->name();
    }

    // PSR-4 lookup
    $logicalPathPsr4 = \strtr($class, '\\', DIRECTORY_SEPARATOR);

    foreach ($prefixesPsr4 as $prefix => $dirs) {
        if (0 === \strpos($class, $prefix)) {
            $dir = $dirs[0];

            return $dir . DIRECTORY_SEPARATOR . \substr($logicalPathPsr4, \strlen($prefix)) . '.php';
        }
    }

    // PSR-0 lookup
    $pos = \strrpos($class, '\\');
    $logicalPathPsr0 = \substr($logicalPathPsr4, 0, $pos + 1)
        . \strtr(\substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);

    foreach ($prefixesPsr0 as $prefix => $dirs) {
        if (0 === \strpos($class, $prefix)) {
            $dir = $dirs[0];

            return $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0 . '.php';
        }
    }

    throw new \RuntimeException('Could not find psr-autoloading path for ' . $class);
}
