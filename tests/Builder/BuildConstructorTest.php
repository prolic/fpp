<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Condition;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildConstructor;

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
            new Argument('strings', 'string', false, true),
            new Argument('floats', 'float', false, true),
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

        $expected = <<<STRING
public function __construct(Name \$name, Age \$age, array \$strings, array \$floats)
    {
        if (strlen(\$name->value()) === 0) {
            throw new \InvalidArgumentException('Name too short');
        }

        if (\$age->value() < 18) {
            throw new \InvalidArgumentException('Too young');
        }

        \$this->name = \$name;
        \$this->age = \$age;
        foreach (\$strings as \$__value) {
            if (! is_string(\$__value)) {
                throw new \InvalidArgumentException('strings expected an array of string');
            }
            \$this->strings[] = \$__value;
        }

        foreach (\$floats as \$__value) {
            if (! is_float(\$__value) && ! is_int(\$__value)) {
                throw new \InvalidArgumentException('floats expected an array of float');
            }
            \$this->floats[] = \$__value;
        }

    }

STRING;

        $this->assertSame($expected, buildConstructor($person, $constructor, new DefinitionCollection($name, $age, $person), ''));
    }
}
