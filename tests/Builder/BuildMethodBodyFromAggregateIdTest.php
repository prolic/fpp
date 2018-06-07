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
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\buildMethodBodyFromAggregateId;

class BuildMethodBodyFromAggregateIdTest extends TestCase
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

        $this->assertSame('return $this->aggregateId();', buildMethodBodyFromAggregateId($argument, $definition, $collection, false));
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

        $this->assertSame('return $this->aggregateId();', buildMethodBodyFromAggregateId($argument, $definition, $collection, false));
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

        $this->assertSame('return \Baz\Something::fromString($this->aggregateId());', buildMethodBodyFromAggregateId($argument, $definition, $collection, false));
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

        $this->assertSame('return Something::fromScalar($this->aggregateId());', buildMethodBodyFromAggregateId($argument, $definition, $collection, false));
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

        buildMethodBodyFromAggregateId($argument, $definition, $collection, false);
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

        buildMethodBodyFromAggregateId($argument, $definition, $collection, false);
    }

    /**
     * @test
     */
    public function it_can_build_with_cache(): void
    {
        $argument = new Argument('name', 'Foo\Arg');

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
            DefinitionType::data(),
            'Foo',
            'Arg',
            [
                new Constructor('Foo\Arg', [
                    new Argument('name', 'string'),
                ]),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $collection = new DefinitionCollection($definition1, $definition2);

        $expected = <<<CODE
if (null === \$this->name) {
            \$this->name = Arg::fromString(\$this->aggregateId());
        }

        return \$this->name;
CODE;

        $this->assertSame($expected, buildMethodBodyFromAggregateId($argument, $definition1, $collection, true));
    }

    /**
     * @test
     */
    public function it_can_build_without_cache(): void
    {
        $argument = new Argument('name', 'Foo\Arg');

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
            DefinitionType::data(),
            'Foo',
            'Arg',
            [
                new Constructor('Foo\Arg', [
                    new Argument('name', 'string'),
                ]),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $collection = new DefinitionCollection($definition1, $definition2);

        $expected = 'return Arg::fromString($this->aggregateId());';

        $this->assertSame($expected, buildMethodBodyFromAggregateId($argument, $definition1, $collection, false));
    }
}
