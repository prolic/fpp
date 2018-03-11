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
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\buildArgumentConstructorFromPayload;

class BuildArgumentConstructorFromPayloadTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_argument_name_if_argument_has_no_type(): void
    {
        $argument = new Argument('name');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);
        $collection = new DefinitionCollection($definition);

        $this->assertSame('$this->payload[\'name\']', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_argument_name_if_argument_is_scalar(): void
    {
        $argument = new Argument('name', 'string');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);
        $collection = new DefinitionCollection($definition);

        $this->assertSame('$this->payload[\'name\']', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_enum(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor1 = new Constructor('Yes');
        $argumentConstructor2 = new Constructor('No');
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor1, $argumentConstructor2], [new Deriving\Enum()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Baz\Something::fromString($this->payload[\'name\'])', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_string(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Something', [new Argument('name', 'string')]);
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor], [new Deriving\FromString()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Baz\Something::fromString($this->payload[\'name\'])', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_uuid(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Baz\Something');
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor], [new Deriving\Uuid()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Baz\Something::fromString($this->payload[\'name\'])', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_scalar(): void
    {
        $argument = new Argument('name', 'Foo\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Foo\Something', [new Argument('age', 'int')]);
        $argumentDefinition = new Definition('Foo', 'Something', [$argumentConstructor], [new Deriving\FromScalar()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('Something::fromScalar($this->payload[\'name\'])', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_array(): void
    {
        $argument = new Argument('name', 'Of\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Something', [new Argument('age', 'int'), new Argument('name', 'string')]);
        $argumentDefinition = new Definition('Of', 'Something', [$argumentConstructor], [new Deriving\FromArray()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('\Of\Something::fromArray($this->payload[\'name\'])', buildArgumentConstructorFromPayload($argument, $definition, $collection));
    }

    /**
     * @test
     */
    public function it_cannot_build_unknown_constructors(): void
    {
        $this->expectException(\RuntimeException::class);

        $argument = new Argument('name', 'Of\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $collection = new DefinitionCollection($definition);

        buildArgumentConstructorFromPayload($argument, $definition, $collection);
    }

    /**
     * @test
     */
    public function it_cannot_build_without_any_deriving(): void
    {
        $this->expectException(\RuntimeException::class);

        $argument = new Argument('name', 'Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Something', [new Argument('name', 'string')]);
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        buildArgumentConstructorFromPayload($argument, $definition, $collection);
    }
}
