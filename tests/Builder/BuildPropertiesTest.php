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
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildProperties;

class BuildPropertiesTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_properties(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Foo\Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor]);

        $expected = <<<STRING
private \$name;
    private \$age;
    private \$whatever;

STRING;

        $this->assertSame($expected, buildProperties($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_properties_for_command(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Foo\Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor], [new Deriving\Command()]);

        $expected = <<<STRING
protected \$messageName = 'Foo\Bar';


STRING;

        $this->assertSame($expected, buildProperties($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_properties_for_command_incl_message_name(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Foo\Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor], [new Deriving\Command()], [], 'foo-bar');

        $expected = <<<STRING
protected \$messageName = 'foo-bar';


STRING;

        $this->assertSame($expected, buildProperties($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_properties_for_domain_event(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Foo\Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor], [new Deriving\DomainEvent()]);

        $expected = <<<STRING
protected \$messageName = 'Foo\Bar';

    private \$name;
    private \$age;
    private \$whatever;

STRING;

        $this->assertSame($expected, buildProperties($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_properties_for_domain_aggregate_changed(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Foo\Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor], [new Deriving\AggregateChanged()]);

        $expected = <<<STRING
protected \$messageName = 'Foo\Bar';

    protected \$payload = [];

    private \$name;
    private \$age;
    private \$whatever;

STRING;

        $this->assertSame($expected, buildProperties($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_returns_placeholder_when_constructor_missing(): void
    {
        $argument1 = new Argument('name', 'string');
        $argument2 = new Argument('age', 'int');
        $argument3 = new Argument('whatever');

        $constructor = new Constructor('Foo\Yeah', [$argument1, $argument2, $argument3]);

        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor], [new Deriving\MicroAggregateChanged()]);

        $this->assertSame('{{properties}}', buildProperties($definition, null, new DefinitionCollection(), '{{properties}}'));
    }
}
