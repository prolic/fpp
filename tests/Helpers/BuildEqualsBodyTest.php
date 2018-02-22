<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\buildEqualsBody;

class BuildEqualsBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_equals_body(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('whatever');
        $argument3 = new Argument('age', 'Unknown');
        $argument4 = new Argument('no', 'Hell\No');
        $argument5 = new Argument('what', 'Hell\What', true);

        $constructor = new Constructor('Hell\Yeah', [$argument1, $argument2, $argument3, $argument4, $argument5]);
        $definition = new Definition('Hell', 'Yeah', [$constructor]);

        $definition2 = new Definition(
            'Hell',
            'No',
            [new Constructor('Hell\No', [new Argument('noman', 'string')])],
            [new Deriving\ToString()]
        );
        $definition3 = new Definition(
            'Hell',
            'What',
            [new Constructor('Hell\What', [new Argument('whatman', 'int')])],
            [new Deriving\Equals()]
        );
        $collection = new DefinitionCollection($definition, $definition2, $definition3);

        $expected = <<<STRING
return \$this->name === \$yeah->name &&
                \$this->whatever === \$yeah->whatever &&
                \$this->value === \$yeah->value &&
                \$this->no->toString() === \$yeah->no->toString() &&
                (null === \$this->what && null === \$yeah->what ||
                    (null !== \$this->what && null !== \$yeah->what &&
                    \$this->what->equals(\$yeah->what))
                );
STRING;

        $this->assertSame($expected, buildEqualsBody($constructor, lcfirst($definition->name()), $collection));
    }
}
