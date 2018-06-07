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
use Fpp\DefinitionType;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildTraits;

class BuildTraitsTest extends TestCase
{
    /**
     * @test
     * @dataProvider derivings
     */
    public function it_builds_traits_for(Deriving $deriving): void
    {
        $constructor = new Constructor('My\Email', [
            new Argument('key', 'string'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Email',
            [$constructor],
            [$deriving]
        );

        $this->assertSame("use \Prooph\Common\Messaging\PayloadTrait;\n", buildTraits($definition, $constructor, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_returns_placeholder_for_aggregate_changed(): void
    {
        $constructor = new Constructor('My\Email', [
            new Argument('key', 'string'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Email',
            [$constructor],
            [new Deriving\AggregateChanged()]
        );

        $this->assertSame('{{traits}}', buildTraits($definition, $constructor, new DefinitionCollection($definition), '{{traits}}'));
    }

    public function derivings(): array
    {
        return [
            [
                new Deriving\Command(),
            ],
            [
                new Deriving\DomainEvent(),
            ],
            [
                new Deriving\Query(),
            ],
        ];
    }
}
