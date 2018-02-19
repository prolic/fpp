<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
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
        $definition = new Definition('Foo', 'Bar', [new Constructor('Bar')]);
        $template = '{{namespace_name}} {{class_name}} ${{variable_name}}';

        $this->assertSame('Foo Bar $bar', replace($definition, $template, new DefinitionCollection($definition)));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_string_constructor(): void
    {
        $definition = new Definition('Foo', 'Bar', [new Constructor('String')]);
        $template = '{{to_string_body}}';

        $this->assertSame('return $this->value;', replace($definition, $template, new DefinitionCollection($definition)));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_constructor_with_string_argument(): void
    {
        $definition = new Definition('Foo', 'Bar', [new Constructor('Bar', [
            new Argument('name', 'string'),
        ])]);
        $template = '{{to_string_body}}';

        $this->assertSame('return $this->value;', replace($definition, $template, new DefinitionCollection($definition)));
    }

    /**
     * @test
     */
    public function it_replaces_to_string_body_for_constructor_with_object_argument(): void
    {
        $definition = new Definition('Foo', 'Bar', [new Constructor('Baz', [
            new Argument('name', 'Baz', false),
        ])]);
        $template = '{{to_string_body}}';

        $this->assertSame('return $this->value->toString();', replace($definition, $template, new DefinitionCollection($definition)));
    }

    /**
     * @test
     */
    public function it_add_abstract_keyword_for_enum_base_class(): void
    {
        $definition = new Definition('Foo', 'Color', [new Constructor('Red'), new Constructor('Blue')], [new Deriving\Enum()]);
        $template = '{{abstract_final}}class Color';

        $this->assertSame('abstract class Color', replace($definition, $template, new DefinitionCollection($definition)));
    }

    /**
     * @test
     * @group by
     */
    public function it_replaces_aggregate_changed(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $email = new Definition(
            'Some',
            'Email',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $definition = new Definition(
            'My',
            'UserRegistered',
            [
                new Constructor('UserRegistered', [
                    new Argument('id', 'My\UserId'),
                    new Argument('name', 'string', true),
                    new Argument('email', 'Some\Email'),
                ]),
            ],
            [
                new Deriving\AggregateChanged(),
            ]
        );

        $template = <<<TEMPLATE
{{abstract_final}}
{{class_extends}}
{{message_name}}
{{arguments}}
{{class_name}}
{{static_constructor_body}}
{{payload_validation}}
{{properties}}
{{accessors}}
TEMPLATE;

        $expected = <<<EXPECTED

 extends \Prooph\Common\Messaging\DomainEvent
My\UserRegistered
string \$id, ?string \$name, string \$email
UserRegistered
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

private \$id;
        private \$name;
        private \$email;

{{accessors}}
EXPECTED;
        //var_dump($expected, replace($definition, $template, new DefinitionCollection($definition))); die;
        $this->assertSame($expected, replace($definition, $template, new DefinitionCollection($definition, $userId, $email)));
    }
}
