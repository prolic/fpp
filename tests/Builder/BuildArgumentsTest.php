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
use function Fpp\Builder\buildArguments;

class BuildArgumentsTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_arguments(): void
    {
        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('string', 'string', false, true),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'UserRegistered',
            [$constructor]
        );

        $expected = 'UserId $id, ?string $name, \Some\Email $email, array $string';
        $this->assertSame($expected, buildArguments($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_arguments_with_default_values(): void
    {
        $constructor = new Constructor('Foo\Bar', [
            new Argument('name', 'string', false, false, '\'test\''),
            new Argument('value', 'int', false, false, '0'),
            new Argument('value2', null, false, false, '\'test2\''),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'Bar',
            [$constructor]
        );

        $expected = 'string $name = \'test\', int $value = 0, $value2 = \'test2\'';
        $this->assertSame($expected, buildArguments($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_exception_arguments(): void
    {
        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('string', 'string', false, true),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'UserRegistered',
            [$constructor],
            [(new Deriving\Exception())->withDefaultMessage('Lorem Ipsum')]
        );

        $expected = 'UserId $id, ?string $name, \Some\Email $email, array $string, string $message = \'Lorem Ipsum\', int $code = 0, \Exception $previous = null';
        $this->assertSame($expected, buildArguments($definition, $constructor, new DefinitionCollection(), ''));
    }
}
