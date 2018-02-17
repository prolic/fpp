<?php

declare (strict_types=1);

namespace FppTest;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving\FromString;
use Fpp\Deriving\ToString;
use function Fpp\dump;
use function Fpp\loadTemplates;
use const Fpp\mapToBodyTemplates;
use const Fpp\mapToClassTemplate;
use const Fpp\replace;
use PHPUnit\Framework\TestCase;

class DumpTest extends TestCase
{
    /**
     * @var callable
     */
    private $dump;

    protected function setUp(): void
    {
        $loadTemplates = function (DefinitionCollection $collection): array {
            return loadTemplates($collection, mapToClassTemplate, mapToBodyTemplates);
        };

        $this->dump = function (DefinitionCollection $collection) use ($loadTemplates): string {
            return dump($collection, $loadTemplates, replace);
        };
    }

    /**
     * @test
     */
    public function it_dumps_string_class(): void
    {
        $dump = $this->dump;

        $definition = new Definition('Foo', 'Bar', [new Constructor('String')]);
        $collection = $this->buildCollection($definition);

        $expected = <<<CODE
namespace Foo {
    class Bar
    {
        private \$value;

        public function __construct(string \$value)
        {
            \$this->value = \$value;
        }

        public function value(): string
        {
            return \$this->value;
        }
    }
}

CODE;

        $this->assertSame($expected, $dump($collection));
    }

    /**
     * @test
     */
    public function it_dumps_string_class_deriving_from_and_to_string(): void
    {
        $dump = $this->dump;

        $definition = new Definition('Foo', 'Bar', [new Constructor('String')], [new FromString(), new ToString()]);
        $collection = $this->buildCollection($definition);

        $expected = <<<CODE
namespace Foo {
    class Bar
    {
        private \$value;

        public function __construct(string \$value)
        {
            \$this->value = \$value;
        }

        public function value(): string
        {
            return \$this->value;
        }

        public static function fromString(string \$bar): Bar
        {
            return new Bar(\$bar);
        }
        public function toString(): string
        {
            return \$this->value;
        }

        public function __toString(): string
        {
            return \$this->value;
        }
    }
}

CODE;

        $this->assertSame($expected, $dump($collection));
    }

    private function buildCollection (Definition ...$definition): DefinitionCollection
    {
        $collection = new DefinitionCollection();

        foreach (func_get_args() as $arg) {
            $collection->addDefinition($arg);
        }

        return $collection;
    }
}
