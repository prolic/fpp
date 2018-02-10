<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Data;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Dumper;
use Fpp\ValueObject;
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

        $code = (new Dumper)->dump($collection);

        echo $code; die;
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

        $code = (new Dumper)->dump($collection);

        echo $code; die;
    }
}
