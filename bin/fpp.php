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

if (! isset($argv[2])) {
    echo 'Missing output file argument';
    exit(1);
}

$path = $argv[1];
$output = $argv[2];

require __DIR__ . '/../src/bootstrap.php';

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
    file_put_contents($output, dump($collection, loadTemplate, replace));
} catch (\RuntimeException $e) {
    echo 'RuntimeException: ' . $e->getMessage();
    exit(1);
}

echo "Successfully generated to and written to '$output'\n";
exit(0);
