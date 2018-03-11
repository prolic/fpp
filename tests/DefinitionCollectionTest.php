<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving\Equals;
use PHPUnit\Framework\TestCase;

class DefinitionCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_definitions(): void
    {
        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);
        $derivings = [new Equals()];
        $definition = new Definition('Foo\Bar', 'Person', [$constructor], $derivings);

        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);

        $this->assertCount(1, $collection->definitions());

        $this->assertTrue($collection->hasDefinition('Foo\Bar', 'Person'));
        $this->assertSame($definition, $collection->definition('Foo\Bar', 'Person'));
        $this->assertNull($collection->definition('Foo\Bar', 'Unknown'));
    }

    /**
     * @test
     */
    public function it_adds_definitions_on_constructor(): void
    {
        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);
        $derivings = [new Equals()];
        $definition = new Definition('Foo\Bar', 'Person', [$constructor], $derivings);

        $collection = new DefinitionCollection($definition);

        $this->assertCount(1, $collection->definitions());

        $this->assertTrue($collection->hasDefinition('Foo\Bar', 'Person'));
        $this->assertSame($definition, $collection->definition('Foo\Bar', 'Person'));
        $this->assertNull($collection->definition('Foo\Bar', 'Unknown'));
    }

    /**
     * @test
     */
    public function it_forbids_duplicate_definitions(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);
        $derivings = [new Equals()];
        $definition = new Definition('Foo\Bar', 'Person', [$constructor], $derivings);

        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);
        $collection->addDefinition($definition);
    }

    /**
     * @test
     */
    public function it_merges_definitions(): void
    {
        $constructor = new Constructor('Foo\Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);
        $derivings = [new Equals()];
        $definition = new Definition('Foo\Bar', 'Person', [$constructor], $derivings);

        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);

        $derivings = [new Equals()];
        $definition = new Definition('Foo\Baz', 'Person', [$constructor], $derivings);

        $collection2 = new DefinitionCollection();
        $collection2->addDefinition($definition);

        $collection3 = $collection->merge($collection2);

        $this->assertNotSame($collection, $collection2);
        $this->assertNotSame($collection2, $collection3);
        $this->assertNotSame($collection, $collection3);

        $this->assertCount(2, $collection3->definitions());
    }

    /**
     * @test
     */
    public function it_forbids_duplicate_definitions_during_merge(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $constructor = new Constructor('Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);
        $derivings = [new Equals()];
        $definition = new Definition('Foo\Bar', 'Person', [$constructor], $derivings);

        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);

        $collection2 = new DefinitionCollection();
        $collection2->addDefinition($definition);

        $collection->merge($collection2);
    }

    /**
     * @test
     */
    public function it_adds_constructor_definition(): void
    {
        $constructor1 = new Constructor('Foo\Bar\Person', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);

        $constructor2 = new Constructor('Foo\Bar\Boss', [
            new Argument('name', 'string', false),
            new Argument('age', 'int', false),
        ]);

        $derivings = [new Equals()];
        $definition = new Definition('Foo\Bar', 'Person', [$constructor1, $constructor2], $derivings);

        $collection = new DefinitionCollection();
        $collection->addDefinition($definition);

        $this->assertTrue($collection->hasConstructorDefinition('Foo\Bar\Boss'));

        $definition2 = $collection->constructorDefinition('Foo\Bar\Boss');

        $this->assertSame($definition, $definition2);
    }
}
