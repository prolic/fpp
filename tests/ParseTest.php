<?php

declare(strict_types=1);

namespace FppTest;

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

    protected function setUp()
    {
        $this->root = vfsStream::setup('test-dir');
        vfsStream::newFile('not_readable.fpp')->withContent('')->at($this->root);
        $this->root->getChild('not_readable.fpp')->chmod('0000');
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

        parse($this->root->url() . '/invalid');
    }

    /**
     * @test
     */
    public function it_throws_if_file_not_readable(): void
    {
        $this->expectException(\RuntimeException::class);

        parse($this->root->url() . '/not_readable.fpp');
    }

    /**
     * @test
     */
    public function it_parses_empty_file(): void
    {
        $contents = '';
        $collection = parse($this->createDefaultFile($contents));

        $this->assertCount(0, $collection->definitions());
    }

    /**
     * @test
     */
    public function it_parses_file_with_only_whitespace(): void
    {
        $contents = '     ';
        $collection = parse($this->createDefaultFile($contents));

        $this->assertCount(0, $collection->definitions());
    }

    /**
     * @test
     */
    public function it_detects_bad_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something Invalid';
        parse($this->createDefaultFile($contents));
    }

    /**
     * @test
     */
    public function it_detects_nested_namespace_declaration(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something{ namespace Foo; }';
        parse($this->createDefaultFile($contents));
    }

    /**
     * @test
     */
    public function it_detects_missing_namespace_end(): void
    {
        $this->expectException(ParseError::class);

        $contents = 'namespace Something ';
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        $collection = parse($this->createDefaultFile($contents));

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
        $collection = parse($this->createDefaultFile($contents));

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
        $collection = parse($this->createDefaultFile($contents));

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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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
        $collection = parse($this->createDefaultFile($contents));

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
        $collection = parse($this->createDefaultFile($contents));

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
        parse($this->createDefaultFile($contents));
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
        parse($this->createDefaultFile($contents));
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

        $collection = parse($this->createDefaultFile($contents));
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
    public function it_detects_invalid_constructor_argument_definitions(): void
    {
        $this->expectException(ParseError::class);

        $contents = <<<CODE
namespace Something;
data Person = Person { string name, ?int \$age } ;
CODE;

        parse($this->createDefaultFile($contents));
    }
}
