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
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildStaticConstructorBody;

class BuildStaticConstructorBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_static_constructor_body_converting_to_payload_without_first_argument(): void
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
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor],
            [new Deriving\AggregateChanged()]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
return new self(\$id->toString(), [
            'name' => \$name,
            'email' => \$email->toString(),
            'something' => \$something,
        ]);
CODE;

        $this->assertSame($expected, buildStaticConstructorBody($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_static_constructor_body_converting_to_payload_with_first_argument(): void
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
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor],
            [new Deriving\Command()]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
return new self([
            'id' => \$id->toString(),
            'name' => \$name,
            'email' => \$email->toString(),
            'something' => \$something,
        ]);
CODE;

        $this->assertSame($expected, buildStaticConstructorBody($definition, $constructor, $collection, ''));
    }
}
