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
use function Fpp\Builder\buildSetters;

class BuildSettersTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_setters(): void
    {
        $constructor = new Constructor('My\Person', [
            new Argument('name', 'string'),
            new Argument('age', 'int'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor]
        );

        $expected = <<<CODE
public function withName(string \$name): Person
    {
        return new self(\$name, \$this->age);
    }

    public function withAge(int \$age): Person
    {
        return new self(\$this->name, \$age);
    }

CODE;

        $this->assertSame($expected, buildSetters($definition, $constructor, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_builds_setters_with_constructor_of_different_type(): void
    {
        $constructor = new Constructor('My\Boss', [
            new Argument('name', 'string'),
            new Argument('age', 'int'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor]
        );

        $expected = <<<CODE
public function withName(string \$name): Person
    {
        return new self(\$name, \$this->age);
    }

    public function withAge(int \$age): Person
    {
        return new self(\$this->name, \$age);
    }

CODE;

        $this->assertSame($expected, buildSetters($definition, $constructor, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_builds_setters_with_constructor_of_different_namespace(): void
    {
        $constructor = new Constructor('Your\Boss', [
            new Argument('name', 'string'),
            new Argument('age', 'int'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor]
        );

        $expected = <<<CODE
public function withName(string \$name): \My\Person
    {
        return new self(\$name, \$this->age);
    }

    public function withAge(int \$age): \My\Person
    {
        return new self(\$this->name, \$age);
    }

CODE;

        $this->assertSame($expected, buildSetters($definition, $constructor, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     * @dataProvider derivings
     */
    public function it_returns_placeholder_for(Deriving $deriving): void
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

        $this->assertSame('{{setters}}', buildSetters($definition, $constructor, new DefinitionCollection($definition), '{{setters}}'));
    }

    /**
     * @test
     */
    public function it_returns_placeholder_when_no_constructor_passed(): void
    {
        $constructor = new Constructor('My\Email', [
            new Argument('key', 'string'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Email',
            [$constructor]
        );

        $this->assertSame('{{setters}}', buildSetters($definition, null, new DefinitionCollection($definition), '{{setters}}'));
    }

    /**
     * @test
     */
    public function it_returns_placeholder_when_no_constructor_arguments_given(): void
    {
        $constructor = new Constructor('My\Email');

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Email',
            [$constructor]
        );

        $this->assertSame('{{setters}}', buildSetters($definition, $constructor, new DefinitionCollection($definition), '{{setters}}'));
    }

    public function derivings(): array
    {
        return [
            [
                new Deriving\AggregateChanged(),
            ],
            [
                new Deriving\Command(),
            ],
            [
                new Deriving\DomainEvent(),
            ],
            [
                new Deriving\Query(),
            ],
            [
                new Deriving\MicroAggregateChanged(),
            ],
            [
                new Deriving\Exception(),
            ],
        ];
    }
}
