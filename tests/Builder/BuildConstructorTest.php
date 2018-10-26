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
use Fpp\DefinitionType;
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
            DefinitionType::data(),
            'Foo\Bar',
            'Name',
            [new Constructor('String')]
        );

        $age = new Definition(
            DefinitionType::data(),
            'Foo\Bar',
            'Age',
            [new Constructor('Int')]
        );

        $email = new Definition(
            DefinitionType::data(),
            'Foo\Bar',
            'Email',
            [new Constructor('String')]
        );

        $constructor = new Constructor('Foo\Bar\Person', [
            new Argument('name', 'Foo\Bar\Name'),
            new Argument('age', 'Foo\Bar\Age'),
            new Argument('strings', 'string', false, true),
            new Argument('floats', 'float', false, true),
            new Argument('emails', 'Foo\Bar\Email', false, true),
        ]);

        $person = new Definition(
            DefinitionType::data(),
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
public function __construct(Name \$name, Age \$age, array \$strings, array \$floats, array \$emails)
    {
        if (strlen(\$name->value()) === 0) {
            throw new \InvalidArgumentException('Name too short');
        }

        if (\$age->value() < 18) {
            throw new \InvalidArgumentException('Too young');
        }

        \$this->name = \$name;
        \$this->age = \$age;

        \$this->strings = [];
        foreach (\$strings as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException('strings expected an array of string');
            }
            \$this->strings[] = \$__value;
        }

        \$this->floats = [];
        foreach (\$floats as \$__value) {
            if (! \is_float(\$__value) && ! \is_int(\$__value)) {
                throw new \InvalidArgumentException('floats expected an array of float');
            }
            \$this->floats[] = \$__value;
        }

        \$this->emails = [];
        foreach (\$emails as \$__value) {
            if (! \$__value instanceof \Foo\Bar\Email) {
                throw new \InvalidArgumentException('emails expected an array of Foo\Bar\Email');
            }
            \$this->emails[] = \$__value;
        }

    }

STRING;

        $this->assertSame($expected, buildConstructor($person, $constructor, new DefinitionCollection($name, $age, $person, $email), ''));
    }
}
