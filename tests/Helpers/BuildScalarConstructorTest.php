<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Constructor;
use Fpp\Definition;
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
        $definition = new Definition('Foo', 'Bar', [$constructor]);

        $this->assertSame('new Bar($value)', buildScalarConstructor($definition));
    }

    /**
     * @test
     * @dataProvider scalarTypeProvider
     */
    public function it_builds_scalar_constructor_from_payload(string $type): void
    {
        $constructor = new Constructor($type);
        $definition = new Definition('Foo', 'Bar', [$constructor]);

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
