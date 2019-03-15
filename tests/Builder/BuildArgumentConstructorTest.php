<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use function Fpp\buildArgumentConstructor;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

class BuildArgumentConstructorTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_argument_name_if_argument_has_no_type(): void
    {
        $argument = new Argument('name');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);
        $collection = new DefinitionCollection($definition);

        $this->assertSame('$name', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_argument_name_if_argument_is_scalar(): void
    {
        $argument = new Argument('name', 'string');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);
        $collection = new DefinitionCollection($definition);

        $this->assertSame('$name', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_enum(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $argumentConstructor1 = new Constructor('Baz\Yes');
        $argumentConstructor2 = new Constructor('Baz\No');
        $argumentDefinition = new Definition(DefinitionType::data(), 'Baz', 'Something', [$argumentConstructor1, $argumentConstructor2], [new Deriving\Enum()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Baz\Something::fromName($name)', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_string(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Baz\Something', [new Argument('name', 'string')]);
        $argumentDefinition = new Definition(DefinitionType::data(), 'Baz', 'Something', [$argumentConstructor], [new Deriving\FromString()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Baz\Something::fromString($name)', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_uuid(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Baz\Something');
        $argumentDefinition = new Definition(DefinitionType::data(), 'Baz', 'Something', [$argumentConstructor], [new Deriving\Uuid()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Baz\Something::fromString($name)', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_scalar(): void
    {
        $argument = new Argument('name', 'Foo\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Foo\Something', [new Argument('age', 'int')]);
        $argumentDefinition = new Definition(DefinitionType::data(), 'Foo', 'Something', [$argumentConstructor], [new Deriving\FromScalar()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('Something::fromScalar($name)', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_array(): void
    {
        $argument = new Argument('name', 'Of\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Of\Something', [new Argument('age', 'int'), new Argument('name', 'string')]);
        $argumentDefinition = new Definition(DefinitionType::data(), 'Of', 'Something', [$argumentConstructor], [new Deriving\FromArray()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Of\Something::fromArray($name)', buildArgumentConstructor($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_cannot_build_unknown_constructors(): void
    {
        $this->expectException(\RuntimeException::class);

        $argument = new Argument('name', 'Of\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $collection = new DefinitionCollection($definition);

        buildArgumentConstructor($argument, $definition, $collection);
    }

    /**
     * @test
     */
    public function it_cannot_build_without_any_deriving(): void
    {
        $this->expectException(\RuntimeException::class);

        $argument = new Argument('name', 'Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Baz\Something', [new Argument('name', 'string')]);
        $argumentDefinition = new Definition(DefinitionType::data(), 'Baz', 'Something', [$argumentConstructor]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        buildArgumentConstructor($argument, $definition, $collection);
    }

    /**
     * @test
     */
    public function it_can_build_enum_as_value(): void
    {
        $constructor1 = new Constructor('My\Red');
        $constructor2 = new Constructor('My\Blue');

        $simpleColor = new Definition(
            DefinitionType::data(),
            'My',
            'SimpleColor',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );

        $constructor1 = new Constructor('My\RED');
        $constructor2 = new Constructor('My\VERY_RED');

        $color = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum(
                [],
                ['useValue']
            )]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('simpleColors', 'My\SimpleColor', false, false),
            new Argument('simpleColorsNullable', 'My\SimpleColor', true, false),
            new Argument('colors', 'My\Color', false, false),
            new Argument('colorsNullable', 'My\Color', true, false),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [new Deriving\ToArray()]
        );

        $collection = new DefinitionCollection($simpleColor, $color);

        $argument = new Argument('simpleColors', 'My\SimpleColor', false, false);
        $expected = <<<CODE
SimpleColor::fromName(\$simpleColors)
CODE;
        $this->assertSame($expected, buildArgumentConstructor($argument, $definition, $collection));

        $argument = new Argument('colors', 'My\Color', false, false);
        $expected = <<<CODE
Color::fromValue(\$colors)
CODE;
        $this->assertSame($expected, buildArgumentConstructor($argument, $definition, $collection));
    }
}
