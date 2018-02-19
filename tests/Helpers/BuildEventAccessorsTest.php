<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use function Fpp\buildEventAccessors;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;

class BuildEventAccessorsTest extends TestCase
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
            if (! isset(\$this->name)) {
                \$this->name = \$this->payload['name'];
            }

            return \$this->name;
        }

        public function age(): ?int
        {
            if (! isset(\$this->age) && isset(\$this->payload['age'])) {
                \$this->age = \$this->payload['age'];
            }

            return \$this->age;
        }

        public function whatever()
        {
            if (! isset(\$this->whatever)) {
                \$this->whatever = \$this->payload['whatever'];
            }

            return \$this->whatever;
        }

STRING;

        $this->assertSame($expected, buildEventAccessors($definition, $collection));
    }
}
