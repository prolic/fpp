<?php

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildArguments;

class BuildArgumentsTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_arguments(): void
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
        $this->assertSame($expected, buildArguments($definition, $constructor, new DefinitionCollection(), ''));
    }
}
