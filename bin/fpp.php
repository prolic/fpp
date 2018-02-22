<?php

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

require __DIR__ . '/../vendor/autoload.php';

$derivingsMap = [
    'AggregateChanged' => new Deriving\AggregateChanged(),
    'Command' => new Deriving\Command(),
    'DomainEvent' => new Deriving\DomainEvent(),
    'Enum' => new Deriving\Enum(),
    'Equals' => new Deriving\Equals(),
    'FromArray' => new Deriving\FromArray(),
    'FromScalar' => new Deriving\FromScalar(),
    'FromString' => new Deriving\FromString(),
    'Query' => new Deriving\Query(),
    'ToArray' => new Deriving\ToArray(),
    'ToScalar' => new Deriving\ToScalar(),
    'ToString' => new Deriving\ToString(),
    'Uuid' => new Deriving\Uuid(),
];

$collection = new DefinitionCollection();

try {
    foreach (scan($path) as $file) {
        $collection = $collection->merge(parse($file, $derivingsMap));
    }
} catch (ParseError $e) {
    echo $e;
    exit(1);
}

file_put_contents($output, dump($collection, loadTemplate, replace));

echo "Successfully generated to and written to '$output'\n";
exit(0);
