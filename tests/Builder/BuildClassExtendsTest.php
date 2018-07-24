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
    public function it_extends_base_exception_class()
    {
        $definition = new Definition(DefinitionType::data(), 'Foo', 'UserNotFound', [new Constructor('Foo\\UserNotFound')], [new Deriving\Exception()]);

        $this->assertSame(
            ' extends \\Exception',
            buildClassExtends($definition, null, new DefinitionCollection($definition), '')
        );
    }

    /**
     * @test
     */
    public function it_extends_custom_exception_class()
    {
        $deriving = new Deriving\Exception('Some\\Custom\\Exception');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'UserNotFound', [new Constructor('Foo\\UserNotFound')], [$deriving]);

        $this->assertSame(
            ' extends Some\\Custom\\Exception',
            buildClassExtends($definition, null, new DefinitionCollection($definition), '')
        );
    }
}
