<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\replace;

class ReplaceTest extends TestCase
{
    /**
     * @test
     */
    public function it_replaces_placeholders_and_cleans_up_formatting(): void
    {
        $builders = [
            'one' => function () {
                return 'private $one;';
            },
            'two' => function () {
                return 'private $two;';
            },
        ];

        $template = <<<TEMPLATE
class Foo
{
    {{one}}
    {{one}}
    {{two}}


    public function bar(): void
    {
    }
        
}

TEMPLATE;

        $expected = <<<TEMPLATE
class Foo
{
    private \$one;
    private \$one;
    private \$two;

    public function bar(): void
    {
    }
}


TEMPLATE;

        $definition = $this->prophesize(Definition::class);
        $constructor = $this->prophesize(Constructor::class);
        $collection = $this->prophesize(DefinitionCollection::class);

        $this->assertSame($expected, replace($template, $definition->reveal(), $constructor->reveal(), $collection->reveal(), $builders));
    }
}
