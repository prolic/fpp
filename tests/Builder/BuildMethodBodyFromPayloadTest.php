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
use function Fpp\buildMethodBodyFromPayload;

class BuildMethodBodyFromPayloadTest extends TestCase
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

        $this->assertSame('return $this->payload[\'name\'];', buildMethodBodyFromPayload($argument, $definition, $collection, false));
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

        $this->assertSame('return $this->payload[\'name\'];', buildMethodBodyFromPayload($argument, $definition, $collection, false));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_enum(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor1 = new Constructor('Baz\Yes');
        $argumentConstructor2 = new Constructor('Baz\No');
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor1, $argumentConstructor2], [new Deriving\Enum()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('return \Baz\Something::fromName($this->payload[\'name\']);', buildMethodBodyFromPayload($argument, $definition, $collection, false));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_string(): void
    {
        $argument = new Argument('name', 'Baz\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Baz\Something', [new Argument('name', 'string')]);
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor], [new Deriving\FromString()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('return \Baz\Something::fromString($this->payload[\'name\']);', buildMethodBodyFromPayload($argument, $definition, $collection, false));
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

        $this->assertSame('return \Baz\Something::fromString($this->payload[\'name\']);', buildMethodBodyFromPayload($argument, $definition, $collection, false));
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

        $this->assertSame('return Something::fromScalar($this->payload[\'name\']);', buildMethodBodyFromPayload($argument, $definition, $collection, false));
    }

    /**
     * @test
     */
    public function it_returns_from_string_constructor_deriving_from_array(): void
    {
        $argument = new Argument('name', 'Of\Something');
        $constructor = new Constructor('Foo\Bar', [$argument]);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $argumentConstructor = new Constructor('Of\Something', [new Argument('age', 'int'), new Argument('name', 'string')]);
        $argumentDefinition = new Definition('Of', 'Something', [$argumentConstructor], [new Deriving\FromArray()]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        $this->assertSame('return \Of\Something::fromArray($this->payload[\'name\']);', buildMethodBodyFromPayload($argument, $definition, $collection, false));
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

        buildMethodBodyFromPayload($argument, $definition, $collection, false);
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

        $argumentConstructor = new Constructor('Baz\Something', [new Argument('name', 'string')]);
        $argumentDefinition = new Definition('Baz', 'Something', [$argumentConstructor]);

        $collection = new DefinitionCollection($definition, $argumentDefinition);

        buildMethodBodyFromPayload($argument, $definition, $collection, false);
    }

    /**
     * @test
     */
    public function it_can_build_with_cache_nullable_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', true, true);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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
if (! isset(\$this->name) && isset(\$this->payload['name'])) {
            \$__returnValue = [];

            foreach (\$this->payload['name'] as \$__value) {
                \$__returnValue[] = Arg::fromString(\$__value);
            }

            \$this->name = \$__returnValue;
        }

        return \$this->name;
CODE;

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, true));
    }

    /**
     * @test
     */
    public function it_can_build_without_cache_nullable_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', true, true);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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
if (! isset(\$this->payload['name'])) {
            return null;
        }

        \$__returnValue = [];

        foreach (\$this->payload['name'] as \$__value) {
            \$__returnValue[] = Arg::fromString(\$__value);
        }

        return \$__returnValue;
CODE;

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, false));
    }

    /**
     * @test
     */
    public function it_can_build_with_cache_not_nullable_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', false, true);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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
if (! isset(\$this->name)) {
            \$__returnValue = [];

            foreach (\$this->payload['name'] as \$__value) {
                \$__returnValue[] = Arg::fromString(\$__value);
            }

            \$this->name = \$__returnValue;
        }

        return \$this->name;
CODE;

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, true));
    }

    /**
     * @test
     */
    public function it_can_build_without_cache_not_nullable_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', false, true);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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
\$__returnValue = [];

        foreach (\$this->payload['name'] as \$__value) {
            \$__returnValue[] = Arg::fromString(\$__value);
        }

        return \$__returnValue;
CODE;

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, false));
    }

    /**
     * @test
     */
    public function it_can_build_with_cache_nullable_no_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', true, false);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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
if (! isset(\$this->name) && isset(\$this->payload['name'])) {
            \$this->name = isset(\$this->payload['name']) ? Arg::fromString(\$this->payload['name']) : null;
        }

        return \$this->name;
CODE;

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, true));
    }

    /**
     * @test
     */
    public function it_can_build_without_cache_nullable_no_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', true, false);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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

        $expected = 'return isset($this->payload[\'name\']) ? Arg::fromString($this->payload[\'name\']) : null;';

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, false));
    }

    /**
     * @test
     */
    public function it_can_build_with_cache_no_nullable_no_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', false, false);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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
if (! isset(\$this->name)) {
            \$this->name = Arg::fromString(\$this->payload['name']);
        }

        return \$this->name;
CODE;

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, true));
    }

    /**
     * @test
     */
    public function it_can_build_without_cache_no_nullable_no_list(): void
    {
        $argument = new Argument('name', 'Foo\Arg', false, false);

        $constructor = new Constructor('Foo\Bar', [$argument]);

        $definition1 = new Definition('Foo', 'Bar', [$constructor]);
        $definition2 = new Definition(
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

        $expected = 'return Arg::fromString($this->payload[\'name\']);';

        $this->assertSame($expected, buildMethodBodyFromPayload($argument, $definition1, $collection, false));
    }
}
