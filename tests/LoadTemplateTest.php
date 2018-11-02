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
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionType;
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
        $constructor = new Constructor('Foo\Bar');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}{{class_implements}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }

    /**
     * @test
     */
    public function it_loads_template_for_string_constructor(): void
    {
        $constructor = new Constructor('String');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}{{class_implements}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
    private \$value;

    public function __construct(string \$value)
    {
        {{scalar_constructor_conditions}}
        \$this->value = \$value;
    }

    public function value(): string
    {
        return \$this->value;
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
        $constructor = new Constructor('Foo\Bar', [new Argument('name', 'string')]);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor], [new Deriving\ToString(), new Deriving\FromString()]);

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}{{class_implements}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
    public function toString(): string
    {
        {{to_string_body}}
    }

    public function __toString(): string
    {
        {{to_string_body}}
    }

    public static function fromString(string \${{argument_name}}): {{class_name}}
    {
        {{from_string_body}}
    }
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }

    /**
     * @test
     */
    public function it_loads_body_template_for_base_enum_class(): void
    {
        $constructor1 = new Constructor('Foo\Blue');
        $constructor2 = new Constructor('Foo\Red');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Color', [$constructor1, $constructor2], [new Deriving\Enum()]);

        $template = loadTemplate($definition, null);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}{{class_implements}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
    public const OPTIONS = [
        {{enum_options}}
    ];

    {{enum_consts}}

    private \$name;
    private \$value;

    private function __construct(string \$name)
    {
        \$this->name = \$name;
        \$this->value = self::OPTIONS[\$name];
    }

    {{enum_constructors}}

    public static function fromName(string \$value): self
    {
        if (! isset(self::OPTIONS[\$value])) {
            throw new \InvalidArgumentException('Unknown enum name given');
        }

        return self::{\$value}();
    }

    public static function fromValue(\$value): self
    {
        foreach (self::OPTIONS as \$name => \$v) {
            if (\$v === \$value) {
                return self::{\$name}();
            }
        }

        throw new \InvalidArgumentException('Unknown enum value given');
    }

    public function equals({{class_name}} \$other): bool
    {
        return \get_class(\$this) === \get_class(\$other) && \$this->name === \$other->name;
    }

    public function name(): string
    {
        return \$this->name;
    }

    public function value()
    {
        return \$this->value;
    }

    public function __toString(): string
    {
        return \$this->name;
    }
    
    public function toString(): string
    {
        return \$this->name;
    }
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }

    /**
     * @test
     */
    public function it_loads_body_template_for_scalar_list_constructors(): void
    {
        $constructor = new Constructor('Float[]');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Color', [$constructor]);

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}{{class_implements}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
    private \$values = [];

    public function __construct(array \$values)
    {
        {{scalar_constructor_conditions}}
        foreach (\$values as \$value) {
            if (! \is_float(\$value) && ! \is_int(\$value)) {
                throw new \InvalidArgumentException('Expected an array of float');
            }
            \$this->values[] = \$value;
        }
    }

    /**
     * @return float[]
     */
    public function values(): array
    {
        return \$this->values;
    }
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }
}
