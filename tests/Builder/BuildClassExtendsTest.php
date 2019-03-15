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

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildClassExtends;

class BuildClassExtendsTest extends TestCase
{
    /**
     * @test
     */
    public function it_extends_base_exception_class(): void
    {
        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'UserNotFound',
            [new Constructor('Foo\\UserNotFound')],
            [new Deriving\Exception()]
        );

        $this->assertSame(
            ' extends \\Exception',
            buildClassExtends($definition, null, new DefinitionCollection($definition), '')
        );
    }

    /**
     * @test
     */
    public function it_extends_exception_class_defined_in_the_global_namespace(): void
    {
        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'UserNotFound',
            [new Constructor('Foo\\UserNotFound')],
            [new Deriving\Exception('\\RuntimeException')]
        );

        $this->assertSame(
            ' extends \\RuntimeException',
            buildClassExtends($definition, null, new DefinitionCollection($definition), '')
        );
    }

    /**
     * @test
     */
    public function it_extends_exception_definition_defined_in_the_current_namespace(): void
    {
        $parent = new Definition(
            DefinitionType::data(),
            'Foo',
            'MyException',
            [new Constructor('Foo\\MyException')],
            [new Deriving\Exception()]
        );
        $child = new Definition(
            DefinitionType::data(),
            'Foo',
            'MyChildException',
            [new Constructor('Foo\\MyChildException')],
            [new Deriving\Exception('MyException')]
        );

        $this->assertSame(
            ' extends MyException',
            buildClassExtends($child, null, new DefinitionCollection($parent, $child), '')
        );
    }

    /**
     * @test
     */
    public function it_extends_exception_definition_defined_in_another_namespace(): void
    {
        $parent = new Definition(
            DefinitionType::data(),
            'Bar',
            'MyException',
            [new Constructor('Bar\\MyException')],
            [new Deriving\Exception()]
        );
        $child = new Definition(
            DefinitionType::data(),
            'Foo',
            'MyChildException',
            [new Constructor('Foo\\MyChildException')],
            [new Deriving\Exception('\\Bar\\MyException')]
        );

        $this->assertSame(
            ' extends \\Bar\\MyException',
            buildClassExtends($child, null, new DefinitionCollection($parent, $child), '')
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_extending_unknown_exception(): void
    {
        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'UserNotFound',
            [new Constructor('Foo\\UserNotFound')],
            [new Deriving\Exception('\\Unknown')]
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('"Foo\\UserNotFound" cannot extend unknown exception "\\Unknown"');
        buildClassExtends($definition, null, new DefinitionCollection($definition), '');
    }
}
