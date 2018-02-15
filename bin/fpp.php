<?php

declare(strict_types=1);

namespace Fpp;

use Fpp\Dumper\AggregateChangedDumper;
use Fpp\Dumper\CommandDumper;
use Fpp\Dumper\DataDumper;
use Fpp\Dumper\DomainEventDumper;
use Fpp\Dumper\EnumDumper;
use Fpp\Dumper\QueryDumper;
use Fpp\Dumper\UuidDumper;

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

$collection = new DefinitionCollection();

foreach (scan($path) as $file) {
    $collection = $collection->merge(parse($file->getRealPath()));
}

$dumper = new DefinitionCollectionDumper([
    'AggregateChanged' => new AggregateChangedDumper(),
    'Data' => new DataDumper($collection),
    'Enum' => new EnumDumper(),
    'Command' => new CommandDumper(),
    'DomainEvent' => new DomainEventDumper(),
    'Query' => new QueryDumper(),
    'Uuid' => new UuidDumper(),
]);
$php = $dumper->dump($collection);

file_put_contents($output, $php);

echo "Successfully generated to and written to '$output'\n";
exit(0);
