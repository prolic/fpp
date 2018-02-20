<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use function Fpp\buildStaticConstructorBodyConvertingToPayload;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

class BuildStaticConstructorBodyConvertingToPayloadTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_static_constructor_body_converting_to_payload(): void
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
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
return new self(\$id->toString(), [
                'name' => \$name,
                'email' => \$email->toString(),
                'something' => \$something,
            ]);

CODE;

        $this->assertSame($expected, buildStaticConstructorBodyConvertingToPayload($constructor, $collection));
    }
}
