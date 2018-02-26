<?php

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildFromArrayBody;

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

            return new self({{arguments}});

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $userId, $email)));
    }

    /**
     * @test
     */
    public function it_replaces_from_array_2(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('String'),
            ]
        );

        $age = new Definition(
            'Some',
            'Age',
            [
                new Constructor('Int'),
            ]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('id', 'My\UserId'),
            new Argument('age', 'Some\Age', true),
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

            \$id = new UserId(\$data['id']);

            if (isset(\$data['age'])) {
                if (! is_int(\$data['age'])) {
                    throw new \InvalidArgumentException("Value for 'age' is not a int in data array");
                }

                \$age = new \Some\Age(\$data['age']);
            } else {
                \$age = null;
            }

            return new self({{arguments}});

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $userId, $age)));
    }

    /**
     * @test
     */
    public function it_replaces_from_array_3(): void
    {
        $name = new Definition(
            'My',
            'Name',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $age = new Definition(
            'Some',
            'Age',
            [
                new Constructor('Int'),
            ],
            [
                new Deriving\FromScalar(),
                new Deriving\ToScalar(),
            ]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('name', 'My\Name'),
            new Argument('age', 'Some\Age', true),
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
if (! isset(\$data['name']) || ! is_string(\$data['name'])) {
                throw new \InvalidArgumentException("Key 'name' is missing in data array or is not a string");
            }

            \$name = Name::fromString(\$data['name']);

            if (isset(\$data['age'])) {
                if (! is_int(\$data['age'])) {
                    throw new \InvalidArgumentException("Value for 'age' is not a int in data array");
                }

                \$age = \Some\Age::fromScalar(\$data['age']);
            } else {
                \$age = null;
            }

            return new self({{arguments}});

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $name, $age)));
    }
}
