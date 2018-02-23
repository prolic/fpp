<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\buildFromArrayBody;

class BuildFromArrayBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_replaces_from_array(): void
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
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['id']) || ! is_string(\$data['id'])) {
                throw new \InvalidArgumentException("Key 'id' is missing in data array or is not a string");
            }

            \$id = UserId::fromString(\$data['id']);

            if (isset(\$data['name'])) {
                if (! is_string(\$data['name'])) {
                    throw new \InvalidArgumentException("Value for 'name' is not a string in data array");
                }

                \$name = \$data['name'];
            } else {
                \$name = null;
            }

            if (! isset(\$data['email']) || ! is_string(\$data['email'])) {
                throw new \InvalidArgumentException("Key 'email' is missing in data array or is not a string");
            }

            \$email = \Some\Email::fromString(\$data['email']);

            return new self(\$id, \$name, \$email);

CODE;

        $this->assertSame($expected, buildFromArrayBody($constructor, $definition, new DefinitionCollection($definition, $userId, $email)));
    }
}
