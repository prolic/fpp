<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use PHPUnit\Framework\TestCase;
use function Fpp\buildArgumentReturnType;

class BuildArgumentReturnTypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_empty_string_if_argument_has_no_type(): void
    {
        $argument = new Argument('name');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertEmpty(buildArgumentReturnType($argument, $definition));
    }

    /**
     * @test
     */
    public function it_returns_scalar_type_hints(): void
    {
        $argument = new Argument('name', 'string');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertSame(': string', buildArgumentReturnType($argument, $definition));
    }

    /**
     * @test
     */
    public function it_returns_nullable_scalar_type_hints(): void
    {
        $argument = new Argument('age', 'int', true);
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertSame(': ?int', buildArgumentReturnType($argument, $definition));
    }

    /**
     * @test
     */
    public function it_returns_return_type_from_same_namespace_as_definition(): void
    {
        $argument = new Argument('name', 'Foo\Baz');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertSame(': Baz', buildArgumentReturnType($argument, $definition));
    }

    /**
     * @test
     */
    public function it_returns_nullable_return_type_from_other_namespace_as_definition(): void
    {
        $argument = new Argument('name', 'Other\Baz', true);
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertSame(': ?\Other\Baz', buildArgumentReturnType($argument, $definition));
    }

    /**
     * @test
     */
    public function it_returns_nullable_return_type_from_other_namespace_as_definition_2(): void
    {
        $argument = new Argument('name', 'Other', true);
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertSame(': ?\Other', buildArgumentReturnType($argument, $definition));
    }
}
