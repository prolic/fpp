<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Deriving\ScalarConverter;
use Fpp\Deriving\StringConverter;
use Fpp\Type\Command;
use Fpp\Type\Data;
use Fpp\Definition;
use Fpp\Type\Enum;
use Fpp\Type\Uuid;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_simple_data_defintion(): void
    {
        $definition = new Definition(new Data(), '', 'Person');

        $this->assertTrue($definition->type()->equals(new Data()));
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

        new Definition(new Data(), '', '');
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_namespace(): void
    {
        $definition = new Definition(new Data(), 'Foo', 'Person');

        $this->assertTrue($definition->type()->equals(new Data()));
        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_arguments(): void
    {
        $definition = new Definition(new Data(), 'Foo', 'Person', [new Argument('name', 'string', false)]);

        $this->assertTrue($definition->type()->equals(new Data()));
        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());
        $this->assertSame('name', (current($definition->arguments()))->name());
        $this->assertSame('string', (current($definition->arguments()))->typeHint());
        $this->assertFalse((current($definition->arguments()))->nullable());
    }

    /**
     * @test
     */
    public function it_creates_data_defintion_with_derivings(): void
    {
        $definition = new Definition(
            new Data(),
            'Foo',
            'Person',
            [new Argument('name', 'string', false)],
            [new ScalarConverter()]
        );

        $this->assertTrue($definition->type()->equals(new Data()));
        $this->assertSame('Foo', $definition->namespace());
        $this->assertSame('Person', $definition->name());
        $this->assertTrue((current($definition->derivings()))->equals(new ScalarConverter()));
    }

    /**
     * @test
     */
    public function it_forbids_message_name_for_data_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(new Data(), 'Foo', 'Person', [], [], 'invalid');
    }

    /**
     * @test
     */
    public function it_forbids_message_name_for_enum_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(new Enum(), 'Foo', 'Color', [], [], 'invalid');
    }

    /**
     * @test
     */
    public function it_forbids_message_name_for_uuid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(new Uuid(), 'Foo', 'PersonId', [], [], 'invalid');
    }

    /**
     * @test
     */
    public function it_forbids_scalar_converter_deriving_for_more_then_one_argument_on_data_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Data(),
            'Foo',
            'Person',
            [
                new Argument('name', 'string', false),
                new Argument('age', 'int', false),
            ],
            [new ScalarConverter()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_string_converter_deriving_for_more_then_one_argument_on_data_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Data(),
            'Foo',
            'Person',
            [
                new Argument('name', 'string', false),
                new Argument('age', 'int', false),
            ],
            [new StringConverter()]
        );
    }

    /**
     * @test
     */
    public function it_requires_arguments_to_be_correct_instance(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Data(),
            'Foo',
            'Person',
            ['invalid']
        );
    }

    /**
     * @test
     */
    public function it_forbids_argument_name_to_be_same_as_definition_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Data(),
            'Foo',
            'Person',
            [new Argument('person', null, null)]
        );
    }

    /**
     * @test
     */
    public function it_forbids_argument_type_hint_for_enums(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Enum(),
            'Foo',
            'Color',
            [new Argument('name', 'int', false)]
        );
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_enum_type_implementation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Enum(),
            'Foo',
            'Color'
        );
    }

    /**
     * @test
     */
    public function it_creates_simple_uuid_type(): void
    {
        $definition = new Definition(
            new Uuid(),
            'Foo',
            'PersonId'
        );

        $this->assertTrue($definition->type()->equals(new Uuid()));
    }

    /**
     * @test
     */
    public function it_forbids_derivings_for_enum_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Enum(),
            'Foo',
            'Color',
            [
                new Argument('Blue', null, null),
                new Argument('Red', null, null)
            ],
            [new ScalarConverter()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_arguments_for_uuid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Uuid(),
            'Foo',
            'PersonId',
            [new Argument('Red', null, null)]
        );
    }

    /**
     * @test
     */
    public function it_forbids_derivings_for_uuid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Uuid(),
            'Foo',
            'PersonId',
            [],
            [new ScalarConverter()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_derivings_for_prooph_message_types(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Command(),
            'Foo',
            'RegisterPerson',
            [],
            [new ScalarConverter()]
        );
    }

    /**
     * @test
     */
    public function it_forbids_empty_message_name_string_for_prooph_message_types(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Definition(
            new Command(),
            'Foo',
            'RegisterPerson',
            [],
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
            new Command(),
            'Foo',
            'RegisterPerson',
            [],
            [],
            'register.person'
        );

        $this->assertTrue($definition->type()->equals(new Command()));
        $this->assertSame('register.person', $definition->messageName());
    }
}
