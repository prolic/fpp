<?php

declare(strict_types=1);

namespace Fpp;

use Fpp\Dumper\AggregateChangedDumper;
use Fpp\Dumper\CommandDumper;
use Fpp\Dumper\DataDumper;
use Fpp\Dumper\EnumDumper;
use Fpp\Dumper\DomainEventDumper;
use Fpp\Dumper\QueryDumper;

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

if (! is_readable($path)) {
    echo "$path is not readable";
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';

$scanner = new Scanner($path);
$parser = new Parser();
$collection = new DefinitionCollection();

foreach ($scanner as $file) {
    /* @var \SplFileInfo $file */
    $definition = $parser->parseFile($file->getRealPath());
    $collection = $collection->merge($definition);
}

$dumper = new DefinitionCollectionDumper([
    'AggregateChanged' => new AggregateChangedDumper(),
    'Data' => new DataDumper(),
    'Enum' => new EnumDumper(),
    'Command' => new CommandDumper(),
    'DomainEvent' => new DomainEventDumper(),
    'Query' => new QueryDumper(),
]);
$php = $dumper->dump($collection);

file_put_contents($output, $php);
