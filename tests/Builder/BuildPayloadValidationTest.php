<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
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
use function Fpp\Builder\buildPayloadValidation;

class BuildPayloadValidationTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_payload_validation_without_first_argument(): void
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

        $floatObject = new Definition(
            DefinitionType::data(),
            'My',
            'FloatObject',
            [
                new Constructor('Float'),
            ],
            [
                new Deriving\FromScalar(),
                new Deriving\ToScalar(),
            ]
        );

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('something', 'Something\Unknown'),
            new Argument('float', 'float'),
            new Argument('floatObject', 'My\FloatObject'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'UserRegistered',
            [$constructor],
            [new Deriving\AggregateChanged()]
        );

        $collection = new DefinitionCollection($userId, $email, $definition, $floatObject);

        $expected = <<<CODE
if (isset(\$payload['name']) && ! \is_string(\$payload['name'])) {
            throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
        }

        if (! isset(\$payload['email']) || ! \is_string(\$payload['email'])) {
            throw new \InvalidArgumentException("Key 'email' is missing in payload or is not a string");
        }

        if (! isset(\$payload['something'])) {
            throw new \InvalidArgumentException("Key 'something' is missing in payload");
        }

        if (! isset(\$payload['float']) || (! \is_float(\$payload['float']) && ! \is_int(\$payload['float']))) {
            throw new \InvalidArgumentException("Key 'float' is missing in payload or is not a float");
        }

        if (! isset(\$payload['floatObject']) || (! \is_float(\$payload['floatObject']) && ! \is_int(\$payload['floatObject']))) {
            throw new \InvalidArgumentException("Key 'floatObject' is missing in payload or is not a float");
        }

CODE;

        $this->assertSame($expected, buildPayloadValidation($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_payload_validation_with_first_argument(): void
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

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'RegisterUser',
            [$constructor],
            [new Deriving\Command()]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
if (! isset(\$payload['id']) || ! \is_string(\$payload['id'])) {
            throw new \InvalidArgumentException("Key 'id' is missing in payload or is not a string");
        }

        if (isset(\$payload['name']) && ! \is_string(\$payload['name'])) {
            throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
        }

        if (! isset(\$payload['email']) || ! \is_string(\$payload['email'])) {
            throw new \InvalidArgumentException("Key 'email' is missing in payload or is not a string");
        }

        if (! isset(\$payload['something'])) {
            throw new \InvalidArgumentException("Key 'something' is missing in payload");
        }

CODE;

        $this->assertSame($expected, buildPayloadValidation($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_payload_validation_with_only_one_argument(): void
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

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'UserRegistered',
            [$constructor],
            [new Deriving\AggregateChanged()]
        );

        $collection = new DefinitionCollection($userId);

        $this->assertSame('payload_validation', buildPayloadValidation($definition, $constructor, $collection, 'payload_validation'));
    }
}
