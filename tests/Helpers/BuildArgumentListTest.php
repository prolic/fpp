<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use PHPUnit\Framework\TestCase;
use function Fpp\buildArgumentList;

class BuildArgumentListTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_argument_list(): void
    {
        $constructor = new Constructor('UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor]
        );

        $expected = 'UserId $id, ?string $name, \Some\Email $email';
        $this->assertSame($expected, buildArgumentList($constructor, $definition));
    }
}
