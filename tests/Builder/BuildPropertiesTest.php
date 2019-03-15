<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use function Fpp\Builder\buildProperties;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

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
public const MESSAGE_NAME = 'Foo\Bar';

    protected \$messageName = self::MESSAGE_NAME;


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
public const MESSAGE_NAME = 'foo-bar';

    protected \$messageName = self::MESSAGE_NAME;


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
public const MESSAGE_NAME = 'Foo\Bar';

    protected \$messageName = self::MESSAGE_NAME;

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
public const MESSAGE_NAME = 'Foo\Bar';

    protected \$messageName = self::MESSAGE_NAME;

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
