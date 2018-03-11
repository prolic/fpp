<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

if (! isset($argv[1])) {
    echo 'Missing input directory or file argument';
    exit(1);
}

$path = $argv[1];

$autoloader = require __DIR__ . '/../src/bootstrap.php';

$prefixesPsr4 = $autoloader->getPrefixesPsr4();
$prefixesPsr0 = $autoloader->getPrefixes();

$locatePsrPath = function (Definition $definition, ?Constructor $constructor) use ($prefixesPsr4, $prefixesPsr0): string {
    return locatePsrPath($prefixesPsr4, $prefixesPsr0, $definition, $constructor);
};

$derivingMap = defaultDerivingMap();

$collection = new DefinitionCollection();

try {
    foreach (scan($path) as $file) {
        $collection = $collection->merge(parse($file, $derivingMap));
    }
} catch (ParseError $e) {
    echo 'Parse Error: ' . $e->getMessage();
    exit(1);
}

try {
    dump($collection, $locatePsrPath, loadTemplate, replace);
} catch (\Exception $e) {
    echo 'Exception: ' . $e->getMessage();
    exit(1);
}

echo "Successfully generated and written to disk\n";
exit(0);
