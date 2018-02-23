<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\ClassKeyword\AbstractKeyword;
use Fpp\ClassKeyword\FinalKeyword;
use Fpp\ClassKeyword\NoKeyword;
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
        $definition = new Definition('Foo', 'Bar', [new Constructor('Foo\Bar')]);
        $template = '{{namespace_name}} {{class_name}} ${{variable_name}}';

        $this->assertSame("Foo Bar \$bar\n", replace($definition, null, $template, new DefinitionCollection($definition), new NoKeyword()));
    }

    /**
     * @test
     */
    public function it_adds_abstract_keyword(): void
    {
        $definition = new Definition('Foo', 'Color', [new Constructor('Foo\Red')]);
        $template = '{{abstract_final}}class Color';

        $this->assertSame("abstract class Color\n", replace($definition, null, $template, new DefinitionCollection($definition), new AbstractKeyword()));
    }

    /**
     * @test
     */
    public function it_adds_final_keyword(): void
    {
        $definition = new Definition('Foo', 'Color', [new Constructor('Foo\Red')]);
        $template = '{{abstract_final}}class Color';

        $this->assertSame("final class Color\n", replace($definition, new Constructor('Red'), $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_adds_no_keyword(): void
    {
        $definition = new Definition('Foo', 'Bar', [new Constructor('Foo\Bar')]);
        $template = '{{abstract_final}}class Bar';

        $this->assertSame("class Bar\n", replace($definition, new Constructor('Foo\Bar'), $template, new DefinitionCollection($definition), new NoKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_aggregate_changed(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
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

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor],
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
final 
 extends \Prooph\Common\Messaging\DomainEvent
My\UserRegistered
UserId \$id, ?string \$name, \Some\Email \$email
UserRegistered
return new self(\$id->toString(), [
                'name' => \$name,
                'email' => \$email->toString(),
            ]);
if (isset(\$payload['name']) && ! is_string(\$payload['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
            }

            if (! isset(\$payload['email']) || ! is_string(\$payload['email'])) {
                throw new \InvalidArgumentException("Key 'email' is missing in payload or is not a string");
            }

private \$id;
        private \$name;
        private \$email;

public function id(): UserId
        {
            if (! isset(\$this->id)) {
                \$this->id = UserId::fromString(\$this->payload['id']);
            }

            return \$this->id;
        }

        public function name(): ?string
        {
            if (! isset(\$this->name) && isset(\$this->payload['name'])) {
                \$this->name = \$this->payload['name'];
            }

            return \$this->name;
        }

        public function email(): \Some\Email
        {
            if (! isset(\$this->email)) {
                \$this->email = \Some\Email::fromString(\$this->payload['email']);
            }

            return \$this->email;
        }


EXPECTED;

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition, $userId, $email), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_command(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
        ]);

        $definition = new Definition(
            'My',
            'RegisterUser',
            [$constructor],
            [
                new Deriving\Command(),
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
final 
 extends \Prooph\Common\Messaging\Command
My\RegisterUser
UserId \$id, ?string \$name
UserRegistered
return new self([
                'id' => \$id->toString(),
                'name' => \$name,
            ]);
if (! isset(\$payload['id']) || ! is_string(\$payload['id'])) {
                throw new \InvalidArgumentException("Key 'id' is missing in payload or is not a string");
            }

            if (isset(\$payload['name']) && ! is_string(\$payload['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
            }

public function id(): UserId
        {
            return UserId::fromString(\$this->payload['id']);
        }

        public function name(): ?string
        {
            return isset(\$this->payload['name']) ? \$this->payload['name'] : null;
        }


EXPECTED;

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition, $userId), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_domain_event(): void
    {
        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'string'),
            new Argument('name', 'string', true),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor],
            [
                new Deriving\DomainEvent(),
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
final 
 extends \Prooph\Common\Messaging\DomainEvent
My\UserRegistered
string \$id, ?string \$name
UserRegistered
return new self([
                'id' => \$id,
                'name' => \$name,
            ]);
if (! isset(\$payload['id']) || ! is_string(\$payload['id'])) {
                throw new \InvalidArgumentException("Key 'id' is missing in payload or is not a string");
            }

            if (isset(\$payload['name']) && ! is_string(\$payload['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
            }

private \$id;
        private \$name;

public function id(): string
        {
            if (! isset(\$this->id)) {
                \$this->id = \$this->payload['id'];
            }

            return \$this->id;
        }

        public function name(): ?string
        {
            if (! isset(\$this->name) && isset(\$this->payload['name'])) {
                \$this->name = \$this->payload['name'];
            }

            return \$this->name;
        }


EXPECTED;

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     * @group by
     */
    public function it_replaces_enum(): void
    {
        $constructor1 = new Constructor('My\Red');
        $constructor2 = new Constructor('My\Blue');

        $definition = new Definition(
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );

        $template = <<<TEMPLATE
{{class_name}}
{{enum_options}}
TEMPLATE;

        $expected = <<<EXPECTED
Color
Red::VALUE => Red::class,
            Blue::VALUE => Blue::class,

EXPECTED;

        $this->assertSame($expected, replace($definition, null, $template, new DefinitionCollection($definition), new AbstractKeyword()));

        $template = '{{enum_value}}';
        $expected = "Red\n";

        $this->assertSame($expected, replace($definition, $constructor1, $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     * @group by
     */
    public function it_replaces_name_spaces_enum(): void
    {
        $constructor1 = new Constructor('My\Color\Red');
        $constructor2 = new Constructor('What\Color\Blue');

        $definition = new Definition(
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );

        $template = <<<TEMPLATE
{{class_name}}
{{enum_options}}
TEMPLATE;

        $expected = <<<EXPECTED
Color
Color\Red::VALUE => Color\Red::class,
            \What\Color\Blue::VALUE => \What\Color\Blue::class,

EXPECTED;

        $this->assertSame($expected, replace($definition, null, $template, new DefinitionCollection($definition), new AbstractKeyword()));

        $template = '{{enum_value}}';
        $expected = "Red\n";

        $this->assertSame($expected, replace($definition, $constructor1, $template, new DefinitionCollection($definition), new FinalKeyword()));

        $template = '{{enum_value}}';
        $expected = "Blue\n";

        $this->assertSame($expected, replace($definition, $constructor2, $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_equals(): void
    {
        $constructor = new Constructor('My\Color', [new Argument('name', 'string')]);
        $definition = new Definition(
            'My',
            'Color',
            [$constructor],
            [new Deriving\Equals()]
        );

        $template = '{{equals_body}}';

        $expected = "return get_class(\$this) === get_class(\$color)\n                && \$this->name === \$color->name;\n";

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_from_scalar(): void
    {
        $definition = new Definition(
            'My',
            'UserId',
            [new Constructor('Int')],
            [new Deriving\FromScalar()]
        );

        $template = '{{type}}';

        $expected = "int\n";

        $this->assertSame($expected, replace($definition, new Constructor('Int'), $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_from_scalar_2(): void
    {
        $constructor = new Constructor('My\UserId', [new Argument('id', 'int')]);

        $definition = new Definition(
            'My',
            'UserId',
            [$constructor],
            [new Deriving\FromScalar()]
        );

        $template = '{{type}}';

        $expected = "int\n";

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_query(): void
    {
        $constructor = new Constructor('My\FindUser', [
            new Argument('id', 'string'),
        ]);

        $definition = new Definition(
            'My',
            'FindUser',
            [$constructor],
            [
                new Deriving\Query(),
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
{{accessors}}
TEMPLATE;

        $expected = <<<EXPECTED
final 
 extends \Prooph\Common\Messaging\Query
My\FindUser
string \$id
FindUser
return new self([
                'id' => \$id,
            ]);
if (! isset(\$payload['id']) || ! is_string(\$payload['id'])) {
                throw new \InvalidArgumentException("Key 'id' is missing in payload or is not a string");
            }

public function id(): string
        {
            return \$this->payload['id'];
        }


EXPECTED;

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_to_array(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
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

        $constructor = new Constructor('My\Person', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
        ]);

        $definition = new Definition(
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\ToArray(),
            ]
        );

        $template = '{{to_array_body}}';

        $expected = <<<CODE
return [
                \$this->id->toString(),
                null === \$this->name ? null : \$this->name,
                \$this->email->toString(),
            ];


CODE;

        $this->assertSame($expected, replace($definition, $constructor, $template, new DefinitionCollection($definition, $userId, $email), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_to_scalar(): void
    {
        $constructor1 = new Constructor('Int');

        $userId = new Definition(
            'My',
            'UserId',
            [$constructor1],
            [new Deriving\ToScalar()]
        );

        $constructor2 = new Constructor('String');

        $email = new Definition(
            'Some',
            'Email',
            [$constructor2],
            [new Deriving\ToScalar()]
        );

        $constructor3 = new Constructor('My\Email', [
            new Argument('key', 'string'),
        ]);

        $definition = new Definition(
            'My',
            'Email',
            [$constructor3],
            [new Deriving\ToScalar()]
        );

        $template = "{{type}}\n{{to_scalar_body}}";
        $expected = "string\nreturn \$this->key;\n\n";

        $this->assertSame($expected, replace($definition, $constructor3, $template, new DefinitionCollection($definition, $userId, $email), new FinalKeyword()));

        $template = "{{type}}\n{{to_scalar_body}}";
        $expected = "string\nreturn \$this->value;\n\n";

        $this->assertSame($expected, replace($email, $constructor2, $template, new DefinitionCollection($definition, $userId, $email), new FinalKeyword()));

        $template = "{{type}}\n{{to_scalar_body}}";
        $expected = "int\nreturn \$this->value;\n\n";

        $this->assertSame($expected, replace($userId, $constructor1, $template, new DefinitionCollection($definition, $userId, $email), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_replaces_to_string(): void
    {
        $constructor1 = new Constructor('String');

        $email = new Definition(
            'Some',
            'Email',
            [$constructor1],
            [new Deriving\ToString()]
        );

        $constructor2 = new Constructor('My\Email', [
            new Argument('key', 'string'),
        ]);

        $definition = new Definition(
            'My',
            'Email',
            [$constructor2],
            [new Deriving\ToString()]
        );

        $template = '{{to_string_body}}';
        $expected = "return \$this->key;\n\n";

        $this->assertSame($expected, replace($definition, $constructor2, $template, new DefinitionCollection($definition, $email), new FinalKeyword()));

        $template = '{{to_string_body}}';
        $expected = "return \$this->value;\n\n";

        $this->assertSame($expected, replace($email, $constructor1, $template, new DefinitionCollection($definition, $email), new FinalKeyword()));
    }
}
