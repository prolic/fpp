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
use function Fpp\Builder\buildFromScalarBody;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

class BuildFromScalarBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_from_scalar_body(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                $constructor = new Constructor('My\UserId', [
                    new Argument('id', 'int'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = <<<CODE
return new self(\$id);

CODE;

        $this->assertSame($expected, buildFromScalarBody($userId, $constructor, new DefinitionCollection($userId), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_scalar_body_from_scalar_constructor(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                $constructor = new Constructor('Int'),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = <<<CODE
return new self(\$userId);

CODE;

        $this->assertSame($expected, buildFromScalarBody($userId, $constructor, new DefinitionCollection($userId), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_object_of_object(): void
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

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = <<<CODE
return new self(UserId::fromScalar(\$userid));

CODE;

        $this->assertSame($expected, buildFromScalarBody($user, $constructor, new DefinitionCollection($userId, $user), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_object_of_object_from_another_namespace(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'Your',
            'UserId',
            [
                new Constructor('Your\UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'Your\UserId'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = <<<CODE
return new self(\Your\UserId::fromScalar(\$userid));

CODE;

        $this->assertSame($expected, buildFromScalarBody($user, $constructor, new DefinitionCollection($userId, $user), ''));
    }

    /**
     * @test
     */
    public function it_builds_from_string_body_from_object_of_object_with_scalar_deriving(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId', [
                    new Argument('id', 'int'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = <<<CODE
return new self(UserId::fromScalar(\$userid));

CODE;

        $this->assertSame($expected, buildFromScalarBody($user, $constructor, new DefinitionCollection($userId, $user), ''));
    }

    /**
     * @test
     */
    public function it_throws_when_no_deriving_given(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
            ]
        );

        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot build fromScalar for My\User, no needed deriving for My\UserId given');

        buildFromScalarBody($user, $constructor, new DefinitionCollection($userId, $user), '');
    }

    /**
     * @test
     */
    public function it_throws_when_unknown_argument_given(): void
    {
        $user = new Definition(
            DefinitionType::data(),
            'My',
            'User',
            [
                $constructor = new Constructor('My\User', [
                    new Argument('userid', 'My\UserId'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot build fromScalar for My\User, unknown argument My\UserId given');

        buildFromScalarBody($user, $constructor, new DefinitionCollection($user), '');
    }

    /**
     * @test
     */
    public function it_returns_placeholder_if_no_constructor_given(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId', [
                    new Argument('id', 'string'),
                ]),
            ],
            [
                new Deriving\FromScalar(),
            ]
        );

        $expected = 'placeholder';

        $this->assertSame($expected, buildFromScalarBody($userId, null, new DefinitionCollection($userId), 'placeholder'));
    }
}
