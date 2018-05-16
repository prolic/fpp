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
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildArgumentName;

class BuildArgumentNameTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_argument_name(): void
    {
        $constructor = new Constructor('Foo\Bar', [
            new Argument('some', 'string'),
        ]);

        $definition = new Definition(
            'Foo',
            'Bar',
            [$constructor]
        );

        $expected = 'some';
        $this->assertSame($expected, buildArgumentName($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_argument_name_from_scalar_constructor(): void
    {
        $constructor = new Constructor('String');

        $definition = new Definition(
            'Foo',
            'Bar',
            [$constructor]
        );

        $expected = 'bar';
        $this->assertSame($expected, buildArgumentName($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_returns_placeholder_if_no_constructor_given(): void
    {
        $constructor = new Constructor('String');

        $definition = new Definition(
            'Foo',
            'Bar',
            [$constructor]
        );

        $expected = 'placeholder';
        $this->assertSame($expected, buildArgumentName($definition, null, new DefinitionCollection(), 'placeholder'));
    }

    /**
     * @test
     */
    public function it_returns_placeholder_if_constructor_has_not_exactly_one_argument(): void
    {
        $constructor = new Constructor('Foo\Bar', [
            new Argument('some', 'string'),
            new Argument('other', 'int'),
        ]);

        $definition = new Definition(
            'Foo',
            'Bar',
            [$constructor]
        );

        $expected = 'placeholder';
        $this->assertSame($expected, buildArgumentName($definition, null, new DefinitionCollection(), 'placeholder'));
    }
}
