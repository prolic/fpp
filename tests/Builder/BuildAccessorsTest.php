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
use function Fpp\Builder\buildAccessors;

class BuildAccessorsTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_payload_accessors(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int', true);
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Hell\Yeah', [$argument1, $argument2, $argument3]);
        $definition = new Definition('Hell', 'Yeah', [$constructor], [new Deriving\Command()]);
        $collection = new DefinitionCollection($definition);

        $expected = <<<STRING
public function name(): string
    {
        return \$this->payload['name'];
    }

    public function age(): ?int
    {
        return isset(\$this->payload['age']) ? \$this->payload['age'] : null;
    }

    public function whatever()
    {
        return \$this->payload['whatever'];
    }

STRING;

        $this->assertSame($expected, buildAccessors($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_event_accessors(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int', true);
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Hell\Yeah', [$argument1, $argument2, $argument3]);
        $definition = new Definition('Hell', 'Yeah', [$constructor], [new Deriving\DomainEvent()]);
        $collection = new DefinitionCollection($definition);

        $expected = <<<STRING
public function name(): string
    {
        if (! isset(\$this->name)) {
            \$this->name = \$this->aggregateId();
        }

        return \$this->name;
    }

    public function age(): ?int
    {
        if (! isset(\$this->age) && isset(\$this->payload['age'])) {
            \$this->age = \$this->payload['age'];
        }

        return \$this->age;
    }

    public function whatever()
    {
        if (! isset(\$this->whatever)) {
            \$this->whatever = \$this->payload['whatever'];
        }

        return \$this->whatever;
    }
STRING;

        $this->assertSame($expected, buildAccessors($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_accessors(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int', true);
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Hell\Yeah', [$argument1, $argument2, $argument3]);
        $definition = new Definition('Hell', 'Yeah', [$constructor]);
        $collection = new DefinitionCollection($definition);

        $expected = <<<STRING
public function name(): string
    {
        return \$this->name;
    }

    public function age(): ?int
    {
        return \$this->age;
    }

    public function whatever()
    {
        return \$this->whatever;
    }

STRING;

        $this->assertSame($expected, buildAccessors($definition, $constructor, $collection, ''));
    }
}
