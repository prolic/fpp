<?php

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildProperties;

class BuildPropertiesTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_properties(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $expected = <<<STRING
private \$name;
        private \$age;
        private \$whatever;

STRING;

        $this->assertSame($expected, buildProperties($definition, $constructor, new DefinitionCollection()));
    }
}
