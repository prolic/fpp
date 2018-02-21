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
        $definition = new Definition('Foo', 'Bar', [new Constructor('Bar')]);
        $template = '{{namespace_name}} {{class_name}} ${{variable_name}}';

        $this->assertSame("Foo Bar \$bar\n", replace($definition, null, $template, new DefinitionCollection($definition), new NoKeyword()));
    }

    /**
     * @test
     */
    public function it_adds_abstract_keyword(): void
    {
        $definition = new Definition('Foo', 'Color', [new Constructor('Red')]);
        $template = '{{abstract_final}}class Color';

        $this->assertSame("abstract class Color\n", replace($definition, null, $template, new DefinitionCollection($definition), new AbstractKeyword()));
    }

    /**
     * @test
     */
    public function it_adds_final_keyword(): void
    {
        $definition = new Definition('Foo', 'Color', [new Constructor('Red')]);
        $template = '{{abstract_final}}class Color';

        $this->assertSame("final class Color\n", replace($definition, new Constructor('Red'), $template, new DefinitionCollection($definition), new FinalKeyword()));
    }

    /**
     * @test
     */
    public function it_adds_no_keyword(): void
    {
        $definition = new Definition('Foo', 'Bar', [new Constructor('Bar')]);
        $template = '{{abstract_final}}class Bar';

        $this->assertSame("class Bar\n", replace($definition, new Constructor('Bar'), $template, new DefinitionCollection($definition), new NoKeyword()));
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

        $constructor = new Constructor('UserRegistered', [
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
}
