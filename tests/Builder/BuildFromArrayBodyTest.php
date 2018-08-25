<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildFromArrayBody;

class BuildFromArrayBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_from_array_body(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
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
            DefinitionType::data(),
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
            new Argument('float', 'float'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['id']) || ! \is_string(\$data['id'])) {
            throw new \InvalidArgumentException("Key 'id' is missing in data array or is not a string");
        }

        \$id = UserId::fromString(\$data['id']);

        if (isset(\$data['name'])) {
            if (! \is_string(\$data['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in data array");
            }

            \$name = \$data['name'];
        } else {
            \$name = null;
        }

        if (! isset(\$data['email']) || ! \is_string(\$data['email'])) {
            throw new \InvalidArgumentException("Key 'email' is missing in data array or is not a string");
        }

        \$email = \Some\Email::fromString(\$data['email']);

        if (! isset(\$data['float']) || (! \is_float(\$data['float']) && ! \is_int(\$data['float']))) {
            throw new \InvalidArgumentException("Key 'float' is missing in data array or is not a float");
        }

        \$float = \$data['float'];

        return new self(
            \$id,
            \$name,
            \$email,
            \$float
        );

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $userId, $email), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_array_body_2(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('String'),
            ]
        );

        $age = new Definition(
            DefinitionType::data(),
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
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['id']) || ! \is_string(\$data['id'])) {
            throw new \InvalidArgumentException("Key 'id' is missing in data array or is not a string");
        }

        \$id = new UserId(\$data['id']);

        if (isset(\$data['age'])) {
            if (! \is_int(\$data['age'])) {
                throw new \InvalidArgumentException("Value for 'age' is not a int in data array");
            }

            \$age = new \Some\Age(\$data['age']);
        } else {
            \$age = null;
        }

        return new self(\$id, \$age);

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $userId, $age), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_array_body_3(): void
    {
        $name = new Definition(
            DefinitionType::data(),
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
            DefinitionType::data(),
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
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['name']) || ! \is_string(\$data['name'])) {
            throw new \InvalidArgumentException("Key 'name' is missing in data array or is not a string");
        }

        \$name = Name::fromString(\$data['name']);

        if (isset(\$data['age'])) {
            if (! \is_int(\$data['age'])) {
                throw new \InvalidArgumentException("Value for 'age' is not a int in data array");
            }

            \$age = \Some\Age::fromScalar(\$data['age']);
        } else {
            \$age = null;
        }

        return new self(\$name, \$age);

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $name, $age), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_array_body_4(): void
    {
        $float1 = new Definition(
            DefinitionType::data(),
            'My',
            'Float1',
            [
                new Constructor('Float'),
            ],
            [
                new Deriving\FromScalar(),
                new Deriving\ToScalar(),
            ]
        );

        $float2 = new Definition(
            DefinitionType::data(),
            'My',
            'Float2',
            [
                new Constructor('Float'),
            ],
            [
                new Deriving\FromScalar(),
                new Deriving\ToScalar(),
            ]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('float1', 'My\Float1'),
            new Argument('float2', 'My\Float2', true),
            new Argument('float3', 'float'),
            new Argument('float4', 'float', true),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['float1']) || (! \is_float(\$data['float1']) && ! \is_int(\$data['float1']))) {
            throw new \InvalidArgumentException("Key 'float1' is missing in data array or is not a float");
        }

        \$float1 = Float1::fromScalar(\$data['float1']);

        if (isset(\$data['float2'])) {
            if (! \is_float(\$data['float2']) && ! \is_int(\$data['float2'])) {
                throw new \InvalidArgumentException("Value for 'float2' is not a float in data array");
            }

            \$float2 = Float2::fromScalar(\$data['float2']);
        } else {
            \$float2 = null;
        }

        if (! isset(\$data['float3']) || (! \is_float(\$data['float3']) && ! \is_int(\$data['float3']))) {
            throw new \InvalidArgumentException("Key 'float3' is missing in data array or is not a float");
        }

        \$float3 = \$data['float3'];

        if (isset(\$data['float4'])) {
            if (! \is_float(\$data['float4']) && ! \is_int(\$data['float4'])) {
                throw new \InvalidArgumentException("Value for 'float4' is not a float in data array");
            }

            \$float4 = \$data['float4'];
        } else {
            \$float4 = null;
        }

        return new self(
            \$float1,
            \$float2,
            \$float3,
            \$float4
        );

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $float1, $float2), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_array_body_5(): void
    {
        $constructor = new Constructor('My\Person', [
            new Argument('floats', 'float', false, true),
            new Argument('strings', 'string', false, true),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['floats']) || ! \is_array(\$data['floats'])) {
            throw new \InvalidArgumentException("Key 'floats' is missing in data array or is not an array");
        }
        
        \$floats = [];

        foreach (\$data['floats'] as \$__value) {
            if (! \is_float(\$__value) && ! \is_int(\$__value)) {
                throw new \InvalidArgumentException("Value for 'floats' in data array is not an array of float");
            }

            \$floats[] = \$__value;
        }

        if (! isset(\$data['strings']) || ! \is_array(\$data['strings'])) {
            throw new \InvalidArgumentException("Key 'strings' is missing in data array or is not an array");
        }
        
        \$strings = [];

        foreach (\$data['strings'] as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException("Value for 'strings' in data array is not an array of string");
            }

            \$strings[] = \$__value;
        }

        return new self(\$floats, \$strings);

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_array_body_6(): void
    {
        $nickname = new Definition(
            DefinitionType::data(),
            'My',
            'Nickname',
            [new Constructor('My\Nickname', [new Argument('nickname', 'string', false, false)])],
            [new Deriving\FromString()]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('floats', 'float', false, true),
            new Argument('strings', 'string', false, true),
            new Argument('nicknames', 'My\Nickname', false, true),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [
                new Deriving\FromArray(),
            ]
        );

        $expected = <<<CODE
if (! isset(\$data['floats']) || ! \is_array(\$data['floats'])) {
            throw new \InvalidArgumentException("Key 'floats' is missing in data array or is not an array");
        }
        
        \$floats = [];

        foreach (\$data['floats'] as \$__value) {
            if (! \is_float(\$__value) && ! \is_int(\$__value)) {
                throw new \InvalidArgumentException("Value for 'floats' in data array is not an array of float");
            }

            \$floats[] = \$__value;
        }

        if (! isset(\$data['strings']) || ! \is_array(\$data['strings'])) {
            throw new \InvalidArgumentException("Key 'strings' is missing in data array or is not an array");
        }
        
        \$strings = [];

        foreach (\$data['strings'] as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException("Value for 'strings' in data array is not an array of string");
            }

            \$strings[] = \$__value;
        }

        if (! isset(\$data['nicknames']) || ! \is_array(\$data['nicknames'])) {
            throw new \InvalidArgumentException("Key 'nicknames' is missing in data array or is not an array");
        }

        \$nicknames = [];

        foreach (\$data['nicknames'] as \$__value) {
            if (! \is_string(\$__value)) {
                throw new \InvalidArgumentException("Value for 'nicknames' in data array is not an array of string");
            }

            \$nicknames[] = Nickname::fromString(\$__value);
        }

        return new self(
            \$floats,
            \$strings,
            \$nicknames
        );

CODE;

        $this->assertSame($expected, buildFromArrayBody($definition, $constructor, new DefinitionCollection($definition, $nickname), ''));
    }

    /**
     * @test
     */
    public function it_returns_place_holder_when_no_constructor_given(): void
    {
        /** @var Definition */
        $definition = $this->prophesize(Definition::class)->reveal();

        /** @var DefinitionCollection */
        $collection = $this->prophesize(DefinitionCollection::class)->reveal();

        $this->assertSame('placeholder', buildFromArrayBody($definition, null, $collection, 'placeholder'));
    }
}
