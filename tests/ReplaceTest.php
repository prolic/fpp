<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\replace;

class ReplaceTest extends TestCase
{
    /**
     * @test
     */
    public function it_replaces_default_values(): void
    {
        $definiton = new Definition('Foo', 'Bar',[new Constructor('Bar')]);
        $template = '{{namespace_name}} {{class_name}} ${{variable_name}}';

        $this->assertEquals('Foo Bar $bar', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_string_constructor(): void
    {
        $definiton = new Definition('Foo', 'Bar', [new Constructor('String')]);
        $template = '{{to_string_body}}';

        $this->assertEquals('return $this->value;', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_constructor_with_string_argument(): void
    {
        $definiton = new Definition('Foo', 'Bar', [new Constructor('Bar', [
            new Argument('name', 'string'),
        ])]);
        $template = '{{to_string_body}}';

        $this->assertEquals('return $this->value;', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_constructor_with_object_argument(): void
    {
        $definiton = new Definition('Foo', 'Bar', [new Constructor('Baz', [
            new Argument('name', 'Baz', false),
        ])]);
        $template = '{{to_string_body}}';

        $this->assertEquals('return $this->value->toString();', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_add_abstract_keyword_for_enum_base_class(): void
    {
        $definiton = new Definition('Foo', 'Color', [new Constructor('Red'), new Constructor('Blue')], [new Deriving\Enum()]);
        $template = '{{abstract_final}}class Color';

        $this->assertEquals('abstract class Color', replace($definiton, $template));
    }
}
