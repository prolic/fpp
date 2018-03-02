<?php

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Condition;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\replace;

class BuildConstructorTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_properties_and_constructor_incl_conditions(): void
    {
        $name = new Definition(
            'Foo\Bar',
            'Name',
            [new Constructor('String')]
        );

        $age = new Definition(
            'Foo\Bar',
            'Age',
            [new Constructor('Int')]
        );

        $constructor = new Constructor('Foo\Bar\Person', [
            new Argument('name', 'Foo\Bar\Name'),
            new Argument('age', 'Foo\Bar\Age'),
        ]);

        $person = new Definition(
            'Foo\Bar',
            'Person',
            [$constructor],
            [],
            [
                new Condition('Person', 'strlen($name->value()) === 0', 'Name too short'),
                new Condition('_', '$age->value() < 18', 'Too young'),
                new Condition('Unknown', '$age->value() < 39', 'Too young'),
            ]
        );

        $template = "{{properties}}\n{{constructor}}";

        $expected = <<<STRING
private \$name;
        private \$age;

public function __construct(Name \$name, Age \$age)
        {
            if (strlen(\$name->value()) === 0) {
                throw new \InvalidArgumentException('Name too short');
            }

            if (\$age->value() < 18) {
                throw new \InvalidArgumentException('Too young');
            }

            \$this->name = \$name;
            \$this->age = \$age;
        }


STRING;

        $this->assertSame($expected, replace($person, $constructor, $template, new DefinitionCollection($name, $age, $person)));
    }
}
