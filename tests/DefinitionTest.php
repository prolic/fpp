<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_simple_data_defintion(): void
    {
        $definition = new Definition('', 'Person');

        $this->assertSame('', $definition->namespace());
        $this->assertSame('Person', $definition->name());
        $this->assertNull($definition->messageName());
    }

    /**
     * @test
     */
    public function it_required_defintion_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition('', '');
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_namespace(): void
    {
        $definition = new Definition('Foo', 'Person');

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_constructor_arguments(): void
    {
        $constructor = new Constructor('Person', [new Argument('name', 'string', false)]);
        $definition = new Definition('Foo', 'Person', [$constructor]);

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());

        $this->assertCount(1, $definition->constructors());

        $constructor = $definition->constructors()[0];

        $this->assertCount(1, $constructor->arguments());

        $argument = $constructor->arguments()[0];

        $this->assertSame('name', $argument->name());
        $this->assertSame('string', $argument->type());
        $this->assertTrue($argument->isScalarTypeHint());
        $this->assertFalse($argument->nullable());
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_derivings(): void
    {
        $constructor = new Constructor('Person', [new Argument('name', 'string', false)]);

        $definition = new Definition(
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToScalar()]
        );

        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());

        $this->assertCount(1, $definition->derivings());

        $deriving = $definition->derivings()[0];

        $this->assertTrue((string) $deriving === Deriving\ToScalar::VALUE);
    }

    /**
     * @test
     */
    public function it_forbids_message_name_for_non_prooph_message_deriving(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition('Foo', 'Person', [], [], [], 'invalid');
    }

    /**
     * @test
     */
    public function it_forbids_scalar_converter_deriving_for_more_then_one_argument_on_data_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);

        new Definition(
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToScalar()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_enum_deriving_equals(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Blue');

        new Definition(
            'Foo',
            'Color',
            [$constructor],
            [new Deriving\Enum(), new Deriving\Equals()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_enum_deriving_to_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Blue');

        new Definition(
            'Foo',
            'Color',
            [$constructor],
            [new Deriving\Enum(), new Deriving\ToString()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_uuid_deriving_equals(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Color',
            [],
            [new Deriving\Uuid(), new Deriving\Equals()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_uuid_deriving_to_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Color',
            [],
            [new Deriving\Uuid(), new Deriving\ToString()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_uuid_deriving_from_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Color',
            [],
            [new Deriving\Uuid(), new Deriving\FromString()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_string_converter_deriving_for_more_then_one_argument_on_data_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);

        new Definition(
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToString()]
        );
    }

    /**
     * @test
     */
    public function it_requires_constructors_to_be_correct_instance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Person',
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_enum_type_implementation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Color',
            [],
            [new Deriving\Enum()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_arguments_for_uuid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            'Foo',
            'PersonId',
            [$constructor],
            [new Deriving\Uuid()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_empty_message_name_string_for_prooph_message_types(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'RegisterPerson',
            [],
            [new Deriving\Command()],
            [],
            ''
        );
    }

    /**
     * @test
     */
    public function it_creates_prooph_message_types(): void
    {
        $definition = new Definition(
            'Foo',
            'RegisterPerson',
            [],
            [new Deriving\Command()],
            [],
            'register.person'
        );

        $this->assertTrue((string) $definition->derivings()[0] === (string) Deriving\Command::VALUE);
        $this->assertSame('register.person', $definition->messageName());
    }

    /**
     * @test
     */
    public function it_forbids_duplicate_constructor_names(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            'Foo',
            'Person',
            [$constructor, $constructor]
        );
    }

    /**
     * @test
     */
    public function it_forbids_invalid_derivings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            'Foo',
            'Person',
            [$constructor],
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_forbids_duplicate_derivings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            'Foo',
            'Person',
            [$constructor],
            [new Deriving\ToString(), new Deriving\ToString()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_invalid_conditions(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            'Foo',
            'Person',
            [$constructor],
            [],
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_forbids_constructor_arguments_for_enums(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor1 = new Constructor('Blue', [
            new Argument('name', 'string', false),
        ]);

        $constructor2 = new Constructor('Red', [
            new Argument('name', 'string', false),
        ]);

        new Definition(
            'Foo',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_to_array_deriving_without_any_constructors(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Color',
            [],
            [new Deriving\ToArray()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_to_string_deriving_without_any_constructors(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            'Foo',
            'Color',
            [],
            [new Deriving\ToString()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_uuid_deriving_with_many_constructors(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor1 = new Constructor('Blue');
        $constructor2 = new Constructor('Red');

        new Definition(
            'Foo',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Uuid()]
        );
    }

    /**
     * @test
     */
    public function it_allow_enums_defintions_without_constructor_arguments(): void
    {
        $constructor1 = new Constructor('Blue');
        $constructor2 = new Constructor('Red');

        $definition = new Definition(
            'Foo',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );

        $this->assertSame(Deriving\Enum::VALUE, $definition->derivings()[0]->__toString());
    }
}
