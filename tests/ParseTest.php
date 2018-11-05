<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Deriving;
use Fpp\ParseError;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use function Fpp\defaultDerivingMap;
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
    private $derivingMap = [];

    protected function setUp()
    {
        $this->root = vfsStream::setup('test-dir');
        vfsStream::newFile('not_readable.fpp')->withContent('')->at($this->root);
        $this->root->getChild('not_readable.fpp')->chmod(0000);

        $this->derivingMap = defaultDerivingMap();
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

        parse($this->root->url() . '/invalid', $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_throws_if_file_not_readable(): void
    {
        $this->expectException(\RuntimeException::class);

        parse($this->root->url() . '/not_readable.fpp', $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_parses_empty_file(): void
    {
        $contents = '';
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

        $this->assertCount(0, $collection->definitions());
    }

    /**
     * @test
     */
    public function it_parses_file_with_only_whitespace(): void
    {
        $contents = '     ';
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

        $this->assertCount(0, $collection->definitions());
    }

    /**
     * @test
     */
    public function it_detects_bad_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something Invalid';
        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_detects_nested_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something{ namespace Foo; }';
        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_detects_missing_namespace_end(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something ';
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

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
    public function it_throws_when_no_namespace_given(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Namespace cannot be empty string');

        $contents = <<<CODE
data Name = String;
CODE;
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

        $this->assertTrue($collection->hasDefinition('Something', 'Name'));

        $definition = $collection->definition('Something', 'Name');
        $this->assertCount(2, $definition->constructors());

        $constructor1 = $definition->constructors()[0];
        $this->assertSame('Something\Name', $constructor1->name());
        $this->assertEmpty($constructor1->arguments());

        $constructor2 = $definition->constructors()[1];
        $this->assertSame('Something\FirstName', $constructor2->name());
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
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

        $this->assertTrue($collection->hasDefinition('Something', 'Name'));
        $this->assertTrue($collection->hasDefinition('Something', 'Age'));

        $definition1 = $collection->definition('Something', 'Name');
        $this->assertCount(2, $definition1->constructors());
        $constructor1 = $definition1->constructors()[0];
        $this->assertSame('Something\Name', $constructor1->name());
        $this->assertEmpty($constructor1->arguments());
        $constructor2 = $definition1->constructors()[1];
        $this->assertSame('Something\FirstName', $constructor2->name());
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
        parse($this->createDefaultFile($contents), $this->derivingMap);
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

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
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
    public function it_reads_constructor_arguments_without_type_hints(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, \$age } ;
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');
        $constructor = $definition->constructors()[0];
        $this->assertCount(2, $constructor->arguments());

        $argument1 = $constructor->arguments()[0];
        $this->assertSame('string', $argument1->type());
        $this->assertSame('name', $argument1->name());
        $this->assertFalse($argument1->nullable());

        $argument2 = $constructor->arguments()[1];

        $this->assertNull($argument2->type());
        $this->assertSame('age', $argument2->name());
        $this->assertFalse($argument2->nullable());
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

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
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

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
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
        $contents = <<<CODE
namespace Something;
data Person = Person { Data\Name \$name }; 
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
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
    public function it_detects_wrong_type_declaration_on_constructor_arguments(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { Data?Name \$name }; 
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_parses_constructor_arguments_with_root_namespace(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { \Data\Name \$name }; 
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
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
    public function it_parses_namespaced_constructors(): void
    {
        $contents = <<<CODE
namespace My {
    data Color = Color\Blue | \What\Color\Red;
}
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('My', 'Color');

        $this->assertCount(2, $definition->constructors());

        $this->assertSame('My\Color\Blue', $definition->constructors()[0]->name());
        $this->assertSame('What\Color\Red', $definition->constructors()[1]->name());
    }

    /**
     * @test
     */
    public function it_parses_namespaced_constructors_2(): void
    {
        $contents = <<<CODE
namespace My {
    data Person = Person { \Other\Email \$email, Name \$name, Age \$age} deriving (ToArray, Equals);
}
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('My', 'Person');

        $this->assertCount(1, $definition->constructors());
        $this->assertSame('My\Person', $definition->constructors()[0]->name());

        $this->assertCount(3, $definition->constructors()[0]->arguments());
        $arguments = $definition->constructors()[0]->arguments();

        $this->assertSame('Other\Email', $arguments[0]->type());
        $this->assertSame('My\Name', $arguments[1]->type());
        $this->assertSame('My\Age', $arguments[2]->type());
    }

    /**
     * @test
     */
    public function it_allows_lower_case_namespace_data_types_constructors_and_type_hints(): void
    {
        $contents = <<<CODE
namespace my {
    data person = person { \\other\\email \$email, name \$name, age \$age} deriving (ToArray, Equals);
}
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('my', 'person');

        $this->assertCount(1, $definition->constructors());
        $this->assertSame('my\person', $definition->constructors()[0]->name());

        $this->assertCount(3, $definition->constructors()[0]->arguments());
        $arguments = $definition->constructors()[0]->arguments();

        $this->assertSame('other\email', $arguments[0]->type());
        $this->assertSame('my\name', $arguments[1]->type());
        $this->assertSame('my\age', $arguments[2]->type());
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

        parse($this->createDefaultFile($contents), $this->derivingMap);
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

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
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

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'DoSomething');

        $derivings = $definition->derivings();
        $this->assertCount(1, $derivings);

        $this->assertSame(Deriving\Command::VALUE, $derivings[0]::VALUE);
        $this->assertNull($definition->messageName());
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

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'DoSomething');

        $derivings = $definition->derivings();
        $this->assertCount(1, $derivings);

        $this->assertSame(Deriving\Command::VALUE, $derivings[0]::VALUE);
        $this->assertSame('do-something', $definition->messageName());
    }

    /**
     * @test
     */
    public function it_throws_on_derivings_with_invalid_syntax_for_message_name_for_prooph_messages(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data DoSomething = DoSomething { string \$name, ?int \$age } deriving (Command:do-something);
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
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

        parse($this->createDefaultFile($contents), $this->derivingMap);
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

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_parses_a_simple_condition(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } where
    | strlen(\$name) === 0 => "Name too short";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(1, $conditions);

        $condition = $conditions[0];
        $this->assertSame('_', $condition->constructor());
        $this->assertSame('strlen($name) === 0', $condition->code());
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
    | strlen(\$name) === 0 => 'Name too short';
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();

        $condition1 = $conditions[0];
        $this->assertSame('_', $condition1->constructor());
        $this->assertSame('strlen($name) === 0', $condition1->code());
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
    | strlen(\$name) === 0 => "Name too short"
    | \$age < 18 => "Too young";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(2, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('_', $condition1->constructor());
        $this->assertSame('strlen($name) === 0', $condition1->code());
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
    strlen(\$name) === 0 => "Name too short";
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
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
    | strlen(\$name) === 0 => Name too short;
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_detects_wrong_condition_syntax_3(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } where
    \ strlen(\$name) === 0 => Name too short;
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_parses_multiple_conditions_for_multiple_constructors(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, int \$age } | Chef { string \$name, } where
    Person:
        | strlen(\$name) === 0 => "Name too short"
        | \$age < 18 => "Too young"
    Chef:
        | strlen(\$name) === 0 => "Name too short";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(3, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('Something\Person', $condition1->constructor());
        $this->assertSame('strlen($name) === 0', $condition1->code());
        $this->assertSame('Name too short', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('Something\Person', $condition2->constructor());
        $this->assertSame('$age < 18', $condition2->code());
        $this->assertSame('Too young', $condition2->errorMessage());

        $condition3 = $conditions[2];
        $this->assertSame('Something\Chef', $condition3->constructor());
        $this->assertSame('strlen($name) === 0', $condition3->code());
        $this->assertSame('Name too short', $condition3->errorMessage());
    }

    /**
     * @test
     */
    public function it_parses_multiple_conditions_for_multiple_constructors_with_defaults(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, int \$age } | Chef { string \$name } where
    Person:
        | \$age < 18 => "Too young"
    _:
        | strlen(\$name) === 0 => "Name too short";
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $conditions = $definition->conditions();
        $this->assertCount(2, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('Something\Person', $condition1->constructor());
        $this->assertSame('$age < 18', $condition1->code());
        $this->assertSame('Too young', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('_', $condition2->constructor());
        $this->assertSame('strlen($name) === 0', $condition2->code());
        $this->assertSame('Name too short', $condition2->errorMessage());
    }

    /**
     * @test
     */
    public function it_parses_derivings_and_conditions(): void
    {
        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name, ?int \$age } deriving (FromArray, ToArray) where
    _:
        | strlen(\$name) < 2 => 'Name too short'
        | isset(\$age) && \$age < 22 => 'Too young';
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $derivings = $definition->derivings();
        $this->assertCount(2, $derivings);

        $this->assertSame('FromArray', $derivings[0]::VALUE);
        $this->assertSame('ToArray', $derivings[1]::VALUE);

        $conditions = $definition->conditions();
        $this->assertCount(2, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('_', $condition1->constructor());
        $this->assertSame('strlen($name) < 2', $condition1->code());
        $this->assertSame('Name too short', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('_', $condition2->constructor());
        $this->assertSame('isset($age) && $age < 22', $condition2->code());
        $this->assertSame('Too young', $condition2->errorMessage());
    }

    /**
     * @test
     */
    public function it_allows_comments(): void
    {
        $contents = <<<CODE
namespace Something;
// just a comment
data Person = Person { string \$name, /* yet another comment */ ?int \$age } deriving (FromArray, ToArray) where
    _:
        /*
        another multi line comment
        */
        | strlen(\$name) < 2 => 'Name too short'
        // one last comment
        | isset(\$age) && \$age < 22 => 'Too young';
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Person');

        $derivings = $definition->derivings();
        $this->assertCount(2, $derivings);

        $this->assertSame('FromArray', $derivings[0]::VALUE);
        $this->assertSame('ToArray', $derivings[1]::VALUE);

        $conditions = $definition->conditions();
        $this->assertCount(2, $conditions);

        $condition1 = $conditions[0];
        $this->assertSame('_', $condition1->constructor());
        $this->assertSame('strlen($name) < 2', $condition1->code());
        $this->assertSame('Name too short', $condition1->errorMessage());

        $condition2 = $conditions[1];
        $this->assertSame('_', $condition2->constructor());
        $this->assertSame('isset($age) && $age < 22', $condition2->code());
        $this->assertSame('Too young', $condition2->errorMessage());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_comma_missing_in_argument_list(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string \$name int \$age };
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_comma_missing_in_argument_list_without_type_hints(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { \$name \$age };
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    /**
     * @test
     */
    public function it_parses_optional_parameters_from_root_namespace(): void
    {
        $contents = <<<CODE
namespace Something {
    data Foo = Foo;
}
namespace Other {
    data Person = Person { ?\Something\Foo \$foo };
}
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Other', 'Person');

        $this->assertTrue($definition->constructors()[0]->arguments()[0]->nullable());
    }

    /**
     * @test
     * @dataProvider scalarListTypes
     */
    public function it_parses_scalar_list_constructors(string $scalarListType): void
    {
        $contents = <<<CODE
namespace Something {
    data Foo = $scalarListType;
}
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Something', 'Foo');

        $this->assertSame($scalarListType, $definition->constructors()[0]->name());
    }

    /**
     * @test
     */
    public function it_parses_enums_with_value_mapping(): void
    {
        $contents = <<<CODE
namespace Foo;
data Color = Blue | Red | Green | Yellow deriving (Enum) with (Blue:[1,2], Red:'r', Green:0, Yellow:['foo' => 'bar', 'baz', 1, true, 'bam' => 123]);
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'Color');
        /** @var Deriving\Enum $deriving */
        $deriving = $definition->derivings()[0];

        $this->assertSame([1, 2], $deriving->valueMapping()['Blue']);
        $this->assertSame('r', $deriving->valueMapping()['Red']);
        $this->assertSame(0, $deriving->valueMapping()['Green']);
        $this->assertSame(['foo' => 'bar', 'baz', 1, true, 'bam' => 123], $deriving->valueMapping()['Yellow']);
    }

    /**
     * @test
     */
    public function it_parses_exception_without_base_class(): void
    {
        $contents = <<<CODE
namespace Foo;
data UserNotFound = UserNotFound deriving (Exception);
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'UserNotFound');
        /** @var Deriving\Exception $deriving */
        $deriving = $definition->derivings()[0];

        $this->assertSame('\\Exception', $deriving->baseClass());
    }

    /**
     * @test
     */
    public function it_parses_exception_with_base_class(): void
    {
        $contents = <<<CODE
namespace Foo;
data UserNotFound = UserNotFound deriving (Exception: \RuntimeException);
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'UserNotFound');
        /** @var Deriving\Exception $deriving */
        $deriving = $definition->derivings()[0];

        $this->assertSame('\\RuntimeException', $deriving->baseClass());
    }

    /**
     * @test
     */
    public function it_parses_exception_with_constructors(): void
    {
        $contents = <<<CODE
namespace Foo;
data UserNotFound = UserNotFound deriving (Exception) with
    | withEmail { string \$email } => 'User with email {{\$email}} cannot be found'
    | create => 'User is nowhere to be found'
    | _ => 'No user found';
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'UserNotFound');
        /** @var Deriving\Exception $deriving */
        $deriving = $definition->derivings()[0];

        $this->assertSame('No user found', $deriving->defaultMessage());
        $ctors = $deriving->constructors();
        $this->assertCount(2, $ctors);

        $this->assertSame('withEmail', $ctors[0]->name());
        $this->assertSame('User with email {{$email}} cannot be found', $ctors[0]->message());
        $args = $ctors[0]->arguments();
        $this->assertCount(1, $args);
        $this->assertEquals(new Argument('email', 'string', false, false), $args[0]);

        $this->assertSame('create', $ctors[1]->name());
        $this->assertSame('User is nowhere to be found', $ctors[1]->message());
        $args = $ctors[1]->arguments();
        $this->assertCount(0, $args);
    }

    /**
     * @test
     */
    public function it_parses_marker(): void
    {
        $contents = <<<CODE
namespace Foo;
marker MyMarker;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'MyMarker');
        $this->assertTrue($definition->isMarker());
    }

    /**
     * @test
     */
    public function it_parses_marker_extending_another_marker_interface(): void
    {
        $contents = <<<CODE
namespace Foo;
marker MyMarkerA;
marker MyMarkerB;
marker MyMarkerC : MyMarkerA, MyMarkerB;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'MyMarkerA');
        $this->assertTrue($definition->isMarker());
        $this->assertCount(0, $definition->markers());

        $definition = $collection->definition('Foo', 'MyMarkerB');
        $this->assertTrue($definition->isMarker());
        $this->assertCount(0, $definition->markers());

        $definition = $collection->definition('Foo', 'MyMarkerC');
        $this->assertTrue($definition->isMarker());
        $this->assertCount(2, $definition->markers());
        $this->assertSame('MyMarkerA', (string) $definition->markers()[0]);
        $this->assertSame('MyMarkerB', (string) $definition->markers()[1]);
    }

    /**
     * @test
     */
    public function it_parses_marker_extending_existing_interfaces(): void
    {
        $contents = <<<CODE
namespace Foo;
marker MyMarker : \App\Abc\Def;
CODE;
        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'MyMarker');
        $this->assertTrue($definition->isMarker());
        $this->assertCount(1, $definition->markers());
        $this->assertSame('\App\Abc\Def', (string) $definition->markers()[0]);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_parsing_data_marked_with_existing_interface(): void
    {
        $contents = <<<CODE
namespace Foo;
data MyData : \App\Abc\Def = MyData;
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);

        $definition = $collection->definition('Foo', 'MyData');
        $this->assertCount(1, $definition->markers());
        $this->assertSame('\App\Abc\Def', (string) $definition->markers()[0]);
    }

    /**
     * @test
     */
    public function it_parses_marked_data(): void
    {
        $contents = <<<CODE
namespace Foo;
marker MyMarkerA;
marker MyMarkerB;
data MyData : MyMarkerA, MyMarkerB = MyData { String \$foo };
CODE;

        $collection = parse($this->createDefaultFile($contents), $this->derivingMap);
        $definition = $collection->definition('Foo', 'MyData');
        $this->assertCount(2, $definition->markers());
        $this->assertSame('MyMarkerA', (string) $definition->markers()[0]);
        $this->assertSame('MyMarkerB', (string) $definition->markers()[1]);
    }

    /**
     * @test
     */
    public function it_throws_exception_when_data_definition_does_not_end_in_semicolon(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Foo;
data Foo = Foo SOME_NONSENSE_TOKEN
CODE;

        parse($this->createDefaultFile($contents), $this->derivingMap);
    }

    public function scalarListTypes(): array
    {
        return [
            ['Bool[]'],
            ['Float[]'],
            ['Int[]'],
            ['String[]'],
        ];
    }
}
