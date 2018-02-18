<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Deriving;
use Fpp\ParseError;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function Fpp\parse;

class ParseTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var array
     */
    private $derivingsMap = [];

    protected function setUp()
    {
        $this->root = vfsStream::setup('test-dir');
        vfsStream::newFile('not_readable.fpp')->withContent('')->at($this->root);
        $this->root->getChild('not_readable.fpp')->chmod('0000');

        $this->derivingsMap = [
            'AggregateChanged' => new Deriving\AggregateChanged(),
            'Command' => new Deriving\Command(),
            'DomainEvent' => new Deriving\DomainEvent(),
            'Enum' => new Deriving\Enum(),
            'Equals' => new Deriving\Equals(),
            'FromArray' => new Deriving\FromArray(),
            'FromScalar' => new Deriving\FromScalar(),
            'FromString' => new Deriving\FromString(),
            'Query' => new Deriving\Query(),
            'ToArray' => new Deriving\ToArray(),
            'ToScalar' => new Deriving\ToScalar(),
            'ToString' => new Deriving\ToString(),
            'Uuid' => new Deriving\Uuid(),
        ];
    }

    protected function createDefaultFile(string $contents): string
    {
        vfsStream::newFile('file.fpp')->withContent($contents)->at($this->root);

        return $this->root->getChild('file.fpp')->url();
    }

    /**
     * @test
     */
    public function it_throws_if_file_not_found(): void
    {
        $this->expectException(\RuntimeException::class);

        parse($this->root->url() . '/invalid', $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_throws_if_file_not_readable(): void
    {
        $this->expectException(\RuntimeException::class);

        parse($this->root->url() . '/not_readable.fpp', $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_parses_empty_file(): void
    {
        $contents = '';
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertCount(0, $collection->definitions());
    }

    /**
     * @test
     */
    public function it_parses_file_with_only_whitespace(): void
    {
        $contents = '     ';
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertCount(0, $collection->definitions());
    }

    /**
     * @test
     */
    public function it_detects_bad_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something Invalid';
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_nested_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something{ namespace Foo; }';
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_namespace_end(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something ';
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_wrong_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);
        $contents = <<<CODE
namespace \;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_parses_string_wrapped_object(): void
    {
        $contents = <<<CODE
namespace Something;
data Name = String;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertTrue($collection->hasDefinition('Something', 'Name'));

        $definition = $collection->definition('Something', 'Name');
        $this->assertCount(1, $definition->constructors());

        $constructor = $definition->constructors()[0];
        $this->assertSame('String', $constructor->name());
        $this->assertEmpty($constructor->arguments());
    }

    /**
     * @test
     */
    public function it_parses_string_wrapped_object_without_namespace(): void
    {
        $contents = <<<CODE
data Name = String;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertTrue($collection->hasDefinition('', 'Name'));
        $definition = $collection->definition('', 'Name');
        $this->assertCount(1, $definition->constructors());
        $constructor = $definition->constructors()[0];
        $this->assertSame('String', $constructor->name());
        $this->assertEmpty($constructor->arguments());
    }

    /**
     * @test
     */
    public function it_parses_from_multiple_namespaces(): void
    {
        $contents = <<<CODE
namespace Foo {
    data Name = String;
}
namespace Bar {
    data Name = String;
}
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertTrue($collection->hasDefinition('Foo', 'Name'));
        $this->assertTrue($collection->hasDefinition('Bar', 'Name'));
    }

    /**
     * @test
     */
    public function it_detects_wrong_declaration_of_second_namespaces(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Foo {
    data Name = String;
}
}
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_end_of_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something\Here;
data Name = String
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_constructor(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Name =;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_data_name(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_data_assignment(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data = ;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_data_assignment_symbol(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person Person;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_wrong_syntax(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
=
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_wrong_syntax2(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
function
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_parses_object_with_two_constructors(): void
    {
        $contents = <<<CODE
namespace Something;
data Name = Name | FirstName;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertTrue($collection->hasDefinition('Something', 'Name'));

        $definition = $collection->definition('Something', 'Name');
        $this->assertCount(2, $definition->constructors());

        $constructor1 = $definition->constructors()[0];
        $this->assertSame('Name', $constructor1->name());
        $this->assertEmpty($constructor1->arguments());

        $constructor2 = $definition->constructors()[1];
        $this->assertSame('FirstName', $constructor2->name());
        $this->assertEmpty($constructor2->arguments());
    }

    /**
     * @test
     */
    public function it_parses_multiple_objects(): void
    {
        $contents = <<<CODE
namespace Something;
data Name = Name | FirstName;
data Age = Int;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);

        $this->assertTrue($collection->hasDefinition('Something', 'Name'));
        $this->assertTrue($collection->hasDefinition('Something', 'Age'));

        $definition1 = $collection->definition('Something', 'Name');
        $this->assertCount(2, $definition1->constructors());
        $constructor1 = $definition1->constructors()[0];
        $this->assertSame('Name', $constructor1->name());
        $this->assertEmpty($constructor1->arguments());
        $constructor2 = $definition1->constructors()[1];
        $this->assertSame('FirstName', $constructor2->name());
        $this->assertEmpty($constructor2->arguments());

        $definition2 = $collection->definition('Something', 'Age');
        $this->assertCount(1, $definition2->constructors());
        $constructor = $definition2->constructors()[0];
        $this->assertSame('Int', $constructor->name());
        $this->assertEmpty($constructor->arguments());
    }

    /**
     * @test
     */
    public function it_detects_wrong_definitions(): void
    {
        $this->expectException(ParseError::class);
        $contents = <<<CODE
namespace Something;
invalid Name = String;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_lower_case_definitions(): void
    {
        $this->expectException(ParseError::class);
        $contents = <<<CODE
namespace Something;
data Name = string;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_reads_constructor_arguments(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } ;
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');
        $constructor = $definition->constructors()[0];
        $this->assertCount(2, $constructor->arguments());

        $argument1 = $constructor->arguments()[0];
        $this->assertSame('string', $argument1->type());
        $this->assertSame('name', $argument1->name());
        $this->assertFalse($argument1->nullable());

        $argument2 = $constructor->arguments()[1];
        $this->assertSame('int', $argument2->type());
        $this->assertSame('age', $argument2->name());
        $this->assertTrue($argument2->nullable());
    }

    /**
     * @test
     */
    public function it_parses_constructors_with_different_amount_of_arguments(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } | Chef { string \$name };
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $constructor1 = $definition->constructors()[0];
        $this->assertCount(2, $constructor1->arguments());

        $argument1 = $constructor1->arguments()[0];
        $this->assertSame('string', $argument1->type());
        $this->assertSame('name', $argument1->name());
        $this->assertFalse($argument1->nullable());

        $argument2 = $constructor1->arguments()[1];
        $this->assertSame('int', $argument2->type());
        $this->assertSame('age', $argument2->name());
        $this->assertTrue($argument2->nullable());

        $constructor2 = $definition->constructors()[1];
        $this->assertCount(1, $constructor2->arguments());

        $argument1 = $constructor2->arguments()[0];
        $this->assertSame('string', $argument1->type());
        $this->assertSame('name', $argument1->name());
        $this->assertFalse($argument1->nullable());
    }

    /**
     * @test
     */
    public function it_parses_constructors_with_arguments_and_without(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } | Chef;
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $constructor1 = $definition->constructors()[0];
        $this->assertCount(2, $constructor1->arguments());

        $argument1 = $constructor1->arguments()[0];
        $this->assertSame('string', $argument1->type());
        $this->assertSame('name', $argument1->name());
        $this->assertFalse($argument1->nullable());

        $argument2 = $constructor1->arguments()[1];
        $this->assertSame('int', $argument2->type());
        $this->assertSame('age', $argument2->name());
        $this->assertTrue($argument2->nullable());

        $constructor2 = $definition->constructors()[1];
        $this->assertCount(0, $constructor2->arguments());
    }

    /**
     * @test
     */
    public function it_parses_constructor_arguments_with_subnamespace(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { Data\Name \$name }; 
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $constructor = $definition->constructors()[0];
        $this->assertCount(1, $constructor->arguments());

        $argument = $constructor->arguments()[0];

        $this->assertSame('Something\Data\Name', $argument->type());
        $this->assertSame('name', $argument->name());
    }

    /**
     * @test
     */
    public function it_parses_constructor_arguments_with_root_namespace(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { \Data\Name \$name }; 
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $constructor = $definition->constructors()[0];
        $this->assertCount(1, $constructor->arguments());

        $argument = $constructor->arguments()[0];

        $this->assertSame('Data\Name', $argument->type());
        $this->assertSame('name', $argument->name());
    }

    /**
     * @test
     */
    public function it_detects_invalid_constructor_argument_definitions(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string name, ?int \$age } ;
CODE;

        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_parses_derivings(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } deriving (ToArray, FromArray);
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $derivings = $definition->derivings();
        $this->assertCount(2, $derivings);

        $this->assertSame(Deriving\ToArray::VALUE, $derivings[0]::VALUE);
        $this->assertSame(Deriving\FromArray::VALUE, $derivings[1]::VALUE);
    }

    /**
     * @test
     */
    public function it_parses_derivings_without_message_name_for_prooph_messages(): void
    {
        $contents = <<<CODE
namespace Something;
data DoSomething = DoSomething { string \$name, ?int \$age } deriving (Command);
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'DoSomething');

        $derivings = $definition->derivings();
        $this->assertCount(1, $derivings);

        $this->assertSame(Deriving\Command::VALUE, $derivings[0]::VALUE);
        $this->assertSame(null, $definition->messageName());
    }

    /**
     * @test
     */
    public function it_parses_derivings_with_message_name_for_prooph_messages(): void
    {
        $contents = <<<CODE
namespace Something;
data DoSomething = DoSomething { string \$name, ?int \$age } deriving (Command:'do-something');
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'DoSomething');

        $derivings = $definition->derivings();
        $this->assertCount(1, $derivings);

        $this->assertSame(Deriving\Command::VALUE, $derivings[0]::VALUE);
        $this->assertSame('do-something', $definition->messageName());
    }

    /**
     * @test
     */
    public function it_parses_derivings_with_message_name_for_prooph_messages_incl_second_deriving(): void
    {
        $contents = <<<CODE
namespace Something;
data DoSomething = DoSomething { string \$name, ?int \$age } deriving (Command:'do-something', Equals);
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'DoSomething');

        $derivings = $definition->derivings();
        $this->assertCount(2, $derivings);

        $this->assertSame(Deriving\Command::VALUE, $derivings[0]::VALUE);
        $this->assertSame(Deriving\Equals::VALUE, $derivings[1]::VALUE);
        $this->assertSame('do-something', $definition->messageName());
    }

    /**
     * @test
     */
    public function it_parses_derivings_with_message_name_for_prooph_messages_incl_second_deriving_2(): void
    {
        $contents = <<<CODE
namespace Something;
data DoSomething = DoSomething { string \$name, ?int \$age } deriving (Equals, Command:'do-something');
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'DoSomething');

        $derivings = $definition->derivings();
        $this->assertCount(2, $derivings);

        $this->assertSame(Deriving\Equals::VALUE, $derivings[0]::VALUE);
        $this->assertSame(Deriving\Command::VALUE, $derivings[1]::VALUE);
        $this->assertSame('do-something', $definition->messageName());
    }

    /**
     * @test
     */
    public function it_detects_wrong_deriving_syntax(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } deriving Equals;
CODE;

        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_throws_on_unknown_derivings(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } deriving (Unknown);
CODE;

        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_parses_a_simple_condition(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } where 
    | strlen(\$name) < 0 => "Name too short";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(1, $conditions);

        $condition = $conditions[0];
        $this->assertSame('_', $condition->constructor());
        $this->assertSame('strlen($name) < 0', $condition->code());
        $this->assertSame('Name too short', $condition->errorMessage());
    }

    /**
     * @test
     */
    public function it_parses_a_simple_condition_2(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } where 
    | strlen(\$name) < 0 => 'Name too short';
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();

        $condition1 = $conditions[0];
        $this->assertSame('_', $condition1->constructor());
        $this->assertSame('strlen($name) < 0', $condition1->code());
        $this->assertSame('Name too short', $condition1->errorMessage());
    }

    /**
     * @test
     */
    public function it_parses_multiple_conditions(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, int \$age } where 
    | strlen(\$name) < 0 => "Name too short"
    | \$age < 18 => "Too young";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(2, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('_', $condition1->constructor());
        $this->assertSame('strlen($name) < 0', $condition1->code());
        $this->assertSame('Name too short', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('_', $condition2->constructor());
        $this->assertSame('$age < 18', $condition2->code());
        $this->assertSame('Too young', $condition2->errorMessage());
    }

    /**
     * @test
     */
    public function it_detects_wrong_condition_syntax(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } where 
    strlen(\$name) < 0 => "Name too short";
CODE;

        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     */
    public function it_detects_wrong_condition_syntax_2(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } where 
    | strlen(\$name) < 0 => Name too short;
CODE;

        parse($this->createDefaultFile($contents), $this->derivingsMap);
    }

    /**
     * @test
     * @group by
     */
    public function it_parses_multiple_conditions_for_multiple_constructors(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, int \$age } | Chef { string \$name, } where
    Person:
        | strlen(\$name) < 0 => "Name too short"
        | \$age < 18 => "Too young"
    Chef:
        | strlen(\$name) < 0 => "Name too short";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(3, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('Person', $condition1->constructor());
        $this->assertSame('strlen($name) < 0', $condition1->code());
        $this->assertSame('Name too short', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('Person', $condition2->constructor());
        $this->assertSame('$age < 18', $condition2->code());
        $this->assertSame('Too young', $condition2->errorMessage());

        $condition3 = $conditions[2];
        $this->assertSame('Chef', $condition3->constructor());
        $this->assertSame('strlen($name) < 0', $condition3->code());
        $this->assertSame('Name too short', $condition3->errorMessage());
    }

    /**
     * @test
     */
    public function it_parses_multiple_conditions_for_multiple_constructors_with_defaults(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, int \$age } | Chef { string \$name, } where
    Person:
        | \$age < 18 => "Too young"
    _:
        | strlen(\$name) < 0 => "Name too short";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingsMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(2, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('Person', $condition1->constructor());
        $this->assertSame('$age < 18', $condition1->code());
        $this->assertSame('Too young', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('_', $condition2->constructor());
        $this->assertSame('strlen($name) < 0', $condition2->code());
        $this->assertSame('Name too short', $condition2->errorMessage());
    }
}
