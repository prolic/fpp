<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionCollectionDumper;
use Fpp\Deriving\ValueObject;
use Fpp\Dumper\AggregateChangedDumper;
use Fpp\Dumper\CommandDumper;
use Fpp\Dumper\DataDumper;
use Fpp\Dumper\DomainEventDumper;
use Fpp\Dumper\EnumDumper;
use Fpp\Dumper\QueryDumper;
use Fpp\Dumper\UuidDumper;
use Fpp\Type\Data;
use PHPUnit\Framework\TestCase;

class DumperTest extends TestCase
{
    /**
     * @test
     */
    public function it_dumps_data(): void
    {
        $arguments = [
            new Argument('name', 'string'),
            new Argument('age', 'int'),
        ];
        $derivings = [(new ValueObject())->value()];
        $definition = new Definition(new Data(), 'Foo\Bar', 'Person', $arguments, $derivings);
        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);

        $dumper = new DefinitionCollectionDumper([
            'AggregateChanged' => new AggregateChangedDumper(),
            'Data' => new DataDumper(),
            'Enum' => new EnumDumper(),
            'Command' => new CommandDumper(),
            'DomainEvent' => new DomainEventDumper(),
            'Query' => new QueryDumper(),
            'Uuid' => new UuidDumper(),
        ]);
        $code = $dumper->dump($collection);

        echo $code;
        die;
    }

    /**
     * @test
     */
    public function it_dumps_data_without_namespace(): void
    {
        $arguments = [
            new Argument('name', 'string'),
            new Argument('age', 'int'),
        ];
        $definition = new Definition(new Data(), '', 'Person', $arguments);
        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);

        $dumper = new DefinitionCollectionDumper([
            'AggregateChanged' => new AggregateChangedDumper(),
            'Data' => new DataDumper(),
            'Enum' => new EnumDumper(),
            'Command' => new CommandDumper(),
            'DomainEvent' => new DomainEventDumper(),
            'Query' => new QueryDumper(),
            'Uuid' => new UuidDumper(),
        ]);
        $code = $dumper->dump($collection);

        echo $code;
        die;
    }
}
