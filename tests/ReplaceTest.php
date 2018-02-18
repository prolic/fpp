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
        $definiton = new Definition('Foo', 'Bar', [new Constructor('Bar')]);
        $template = '{{namespace_name}} {{class_name}} ${{variable_name}}';

        $this->assertSame('Foo Bar $bar', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_string_constructor(): void
    {
        $definiton = new Definition('Foo', 'Bar', [new Constructor('String')]);
        $template = '{{to_string_body}}';

        $this->assertSame('return $this->value;', replace($definiton, $template));
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

        $this->assertSame('return $this->value;', replace($definiton, $template));
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

        $this->assertSame('return $this->value->toString();', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_add_abstract_keyword_for_enum_base_class(): void
    {
        $definiton = new Definition('Foo', 'Color', [new Constructor('Red'), new Constructor('Blue')], [new Deriving\Enum()]);
        $template = '{{abstract_final}}class Color';

        $this->assertSame('abstract class Color', replace($definiton, $template));
    }

    /**
     * @test
     */
    public function it_replaces_aggregate_changed(): void
    {
        $definition = new Definition(
            'My',
            'UserRegistered',
            [
                new Constructor('UserRegistered', [
                    new Argument('id', 'string'),
                    new Argument('name', 'string', true),
                    new Argument('email', 'string'),
                ]),
            ],
            [
                new Deriving\AggregateChanged(),
            ]
        );

        $template = <<<TEMPLATE
{{abstract_final}} {{class_extends}} {{message_name}} {{arguments}} {{class_name}}
{{static_constructor_body}}
            {{payload_validation}}
TEMPLATE;

        $expected = <<<EXPECTED
  extends \Prooph\Common\Messaging\DomainEvent My\UserRegistered string \$id, ?string \$name, string \$email UserRegistered
return new self(\$id, [
                'name' => \$name,
                'email' => \$email,
            ]);
            if (isset(\$payload['name']) && ! is_string(\$payload['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
            }

            if (! isset(\$payload['email'])) {
                throw new \InvalidArgumentException("Key 'email' is missing in payload");
            }

            if (! is_string(\$payload['email'])) {
                throw new \InvalidArgumentException("Value for 'email' is not a string in payload");
            }

EXPECTED;

        $this->assertSame($expected, replace($definition, $template));
    }
}
