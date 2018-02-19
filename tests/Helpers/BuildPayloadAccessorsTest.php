<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use function Fpp\buildEventAccessors;
use function Fpp\buildPayloadAccessors;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;

class BuildPayloadAccessorsTest extends TestCase
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
                return \$this->payload['name'];
            }

            public function age(): ?int
            {
                return isset(\$this->payload['age']) ? \$this->payload['age'] : null;
            }

            public function whatever()
            {
                return \$this->payload['whatever'];
            }

STRING;

        $this->assertSame($expected, buildPayloadAccessors($definition, $collection));
    }
}
