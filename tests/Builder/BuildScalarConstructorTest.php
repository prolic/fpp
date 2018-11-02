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
use Fpp\DefinitionType;
use PHPUnit\Framework\TestCase;
use function Fpp\buildScalarConstructor;
use function Fpp\buildScalarConstructorFromPayload;

class BuildScalarConstructorTest extends TestCase
{
    /**
     * @test
     * @dataProvider scalarTypeProvider
     */
    public function it_builds_scalar_constructor(string $type): void
    {
        $constructor = new Constructor($type);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $this->assertSame('new Bar($value)', buildScalarConstructor($definition));
    }

    /**
     * @test
     * @dataProvider scalarTypeProvider
     */
    public function it_builds_scalar_constructor_from_payload(string $type): void
    {
        $constructor = new Constructor($type);
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $this->assertSame('new Bar($this->payload[\'value\'])', buildScalarConstructorFromPayload($definition));
    }

    public function scalarTypeProvider(): array
    {
        return [
            ['String'],
            ['Int'],
            ['Bool'],
            ['Float'],
        ];
    }
}
