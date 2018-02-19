<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\buildAccessors;

class BuildAccessorsTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_event_accessors(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int', true);
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Yeah', [$argument1, $argument2, $argument3]);
        $definition = new Definition('Hell', 'Yeah', [$constructor]);
        $collection = new DefinitionCollection($definition);

        $expected = <<<STRING
public function name(): string
        {
            return \$this->name;
        }

        public function age(): ?int
        {
            return \$this->age;
        }

        public function whatever()
        {
            return \$this->whatever;
        }

STRING;

        $this->assertSame($expected, buildAccessors($definition, $collection));
    }
}
