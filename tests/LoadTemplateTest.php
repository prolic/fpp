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

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}
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
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
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

        $template = loadTemplate($definition, $constructor);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}
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

    public static function fromString(string \${{variable_name}}): {{class_name}}
    {
        return new {{class_name}}(\${{variable_name}});
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
        $constructor1 = new Constructor('Blue');
        $constructor2 = new Constructor('Red');
        $definition = new Definition('Foo', 'Color', [$constructor1, $constructor2], [new Deriving\Enum()]);

        $template = loadTemplate($definition, null);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
    const OPTIONS = [
        {{enum_options}}
    ];

    final public function __construct()
    {
        \$valid = false;

        foreach (self::OPTIONS as \$value) {
            if (\$this instanceof \$value) {
                \$valid = true;
                break;
            }
        }

        if (! \$valid) {
            \$self = get_class(\$this);
            throw new \LogicException("Invalid {{class_name}} '\$self' given");
        }
    }

    public static function fromString(string \$value): self
    {
        if (! isset(self::OPTIONS[\$value])) {
            throw new \InvalidArgumentException('Unknown enum value given');
        }

        \$class = self::OPTIONS[\$value];

        return new \$class();
    }

    public function equals({{class_name}} \$other): bool
    {
        return get_class(\$this) === get_class(\$other);
    }

    public function toString(): string
    {
        return static::VALUE;
    }

    public function __toString(): string
    {
        return static::VALUE;
    }
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }

    /**
     * @test
     */
    public function it_loads_body_template_for_enum_instance_class(): void
    {
        $constructor1 = new Constructor('Blue');
        $constructor2 = new Constructor('Red');
        $definition = new Definition('Foo', 'Color', [$constructor1, $constructor2], [new Deriving\Enum()]);

        $template = loadTemplate($definition, $constructor2);

        $expected = <<<TEMPLATE
namespace {{namespace}};

{{class_keyword}}class {{class_name}}{{class_extends}}
{
    {{traits}}
    {{properties}}
    {{constructor}}
    {{accessors}}
    {{setters}}
    const VALUE = '{{enum_value}}';
}

TEMPLATE;

        $this->assertSame($expected, $template);
    }
}
