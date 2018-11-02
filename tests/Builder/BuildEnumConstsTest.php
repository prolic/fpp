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
use function Fpp\Builder\buildEnumConsts;

class BuildEnumConstsTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_enum_consts_without_value_mappings(): void
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
public const Red = 0;
    public const Blue = 1;
EXPECTED;

        $this->assertSame($expected, buildEnumConsts($definition, null, new DefinitionCollection($definition), 'enum_consts'));
    }

    /**
     * @test
     */
    public function it_builds_enum_consts_with_value_mappings(): void
    {
        $constructor1 = new Constructor('My\Red');
        $constructor2 = new Constructor('My\Blue');

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum(
                [
                    'Red' => 'red',
                    'Blue' => 'blue',
                ]
            )]
        );

        $expected = <<<EXPECTED
public const Red = 'red';
    public const Blue = 'blue';
EXPECTED;

        $this->assertSame($expected, buildEnumConsts($definition, null, new DefinitionCollection($definition), 'enum_consts'));
    }
}
