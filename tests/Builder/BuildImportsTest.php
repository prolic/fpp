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
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildImports;

class BuildImportsTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_builds_imports_for(Deriving $deriving, string $expected): void
    {
        if ($deriving->equals(new Deriving\Uuid())) {
            $arguments = [];
        } else {
            $arguments = [new Argument('something', 'string')];
        }
        $constructor = new Constructor('Foo\Bar', $arguments);
        $definition = new Definition('Foo', 'Bar', [$constructor], [$deriving]);

        $this->assertSame($expected, buildImports($definition, $constructor, new DefinitionCollection($definition), '{{imports}}'));
    }

    public function dataProvider(): array
    {
        return [
            [
                new Deriving\Uuid(),
                "use Ramsey\Uuid\Uuid;\n    use Ramsey\Uuid\UuidInterface;\n",
            ],
            [
                new Deriving\AggregateChanged(),
                "use Prooph\Common\Messaging\DomainEvent;\n",
            ],
            [
                new Deriving\Command(),
                "use Prooph\Common\Messaging\Command;\n    use Prooph\Common\Messaging\PayloadTrait;\n",
            ],
            [
                new Deriving\DomainEvent(),
                "use Prooph\Common\Messaging\DomainEvent;\n    use Prooph\Common\Messaging\PayloadTrait;\n",
            ],
            [
                new Deriving\Query(),
                "use Prooph\Common\Messaging\PayloadTrait;\n    use Prooph\Common\Messaging\Query;\n",
            ],
            [
                new Deriving\Equals(),
                '{{imports}}',
            ],
        ];
    }
}
