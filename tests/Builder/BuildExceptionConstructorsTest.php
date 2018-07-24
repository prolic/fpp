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
use Fpp\ExceptionConstructor;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildExceptionConstructors;

class BuildExceptionConstructorsTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_exception_constructors()
    {
        $constructor = new Constructor('App\\Foo', [
            new Argument('email', 'string'),
        ]);
        $definition = new Definition(
            DefinitionType::data(),
            'App\\',
            'Foo',
            [$constructor],
            [(new Deriving\Exception())->withConstructors([new ExceptionConstructor('create', [new Argument('foo', 'int'), new Argument('email', 'string')], 'Something is wrong!')])]
        );

        $expected = <<<STRING

    public static function create(int \$foo, string \$email, int \$code = 0, \Exception \$previous = null): self
    {
        return new self(\$email, sprintf('Something is wrong!'), \$code, \$previous);
    }
STRING;
        $this->assertSame($expected, buildExceptionConstructors($definition, $constructor, new DefinitionCollection(), 'placeholder'));
    }

    /**
     * @test
     */
    public function it_ignores_definition_without_constructor()
    {
        $definition = new Definition(
            DefinitionType::data(),
            'App\\',
            'Foo',
            [new Constructor('String')]
        );

        $this->assertSame('placeholder', buildExceptionConstructors($definition, null, new DefinitionCollection(), 'placeholder'));
    }

    /**
     * @test
     */
    public function it_ignores_non_deriving_exception_definition()
    {
        $constructor = new Constructor('String');
        $definition = new Definition(
            DefinitionType::data(),
            'App\\',
            'Foo',
            [$constructor]
        );

        $this->assertSame('placeholder', buildExceptionConstructors($definition, $constructor, new DefinitionCollection(), 'placeholder'));
    }
}
