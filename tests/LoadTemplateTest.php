<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\loadTemplate;

class LoadTemplateTest extends TestCase
{
    /**
     * @test
     */
    public function it_loads_default_class_template(): void
    {
        $constructor = new Constructor('Bar');
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $template = loadTemplate($definition);

        $expected = <<<TEMPLATE
namespace {{namespace_name}} {
    class {{class_name}}{{class_extends}}
    {
    }
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }

    /**
     * @test
     */
    public function it_loads_class_template_with_deriving_body_templates(): void
    {
        $constructor = new Constructor('Bar', [new Argument('name', 'string')]);
        $definition = new Definition('Foo', 'Bar', [$constructor], [new Deriving\ToString(), new Deriving\FromString()]);

        $template = loadTemplate($definition);

        $expected = <<<TEMPLATE
namespace {{namespace_name}} {
    class {{class_name}}{{class_extends}}
    {
        public function toString(): string
        {
            {{to_string_body}}
        }

        public function __toString(): string
        {
            {{to_string_body}}
        }

        public static function fromString(string \${{variable_name}}): {{class_name}}
        {
            return new {{class_name}}(\${{variable_name}});
        }
    }
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }
}
