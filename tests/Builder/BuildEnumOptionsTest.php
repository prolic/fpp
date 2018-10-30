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
use Fpp\InvalidDeriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildEnumOptions;

class BuildEnumOptionsTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_enum_options(): void
    {
        $constructor1 = new Constructor('My\Red');
        $constructor2 = new Constructor('My\Blue');

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );

        $expected = <<<EXPECTED
'Red' => 0,
        'Blue' => 1,
EXPECTED;

        $this->assertSame($expected, buildEnumOptions($definition, null, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_builds_enum_options_with_value_mapping(): void
    {
        $constructor1 = new Constructor('My\Red');
        $constructor2 = new Constructor('My\Blue');

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum([
                'Red' => 'someThing',
                'Blue' => [
                    'foo' => 'bar',
                ],
            ])]
        );

        $expected = <<<EXPECTED
'Red' => 'someThing',
        'Blue' => [
            'foo' => 'bar',
        ],
EXPECTED;

        $this->assertSame($expected, buildEnumOptions($definition, null, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_builds_enum_options_withValue(): void
    {
        $constructor1 = new Constructor('My\RED');
        $constructor2 = new Constructor('My\VERY_RED');

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum(
                [],
                ['withValue']
            )]
        );

        $expected = <<<EXPECTED
'RED' => 'RED',
        'VERY_RED' => 'VERY_RED',
EXPECTED;

        $this->assertSame($expected, buildEnumOptions($definition, null, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_does_not_allow_enum_options_with_namespaces(): void
    {
        $this->expectException(InvalidDeriving::class);

        $constructor1 = new Constructor('My\Color\Red');
        $constructor2 = new Constructor('What\Color\Blue');

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );

        buildEnumOptions($definition, null, new DefinitionCollection($definition), '');
    }
}
