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
use function Fpp\Builder\buildEqualsBody;

class BuildEqualsBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_equals_body(): void
    {
        $arguments = [];
        $arguments[] = new Argument('name', 'string');
        $arguments[] = new Argument('whatever');
        $arguments[] = new Argument('age', 'Some\Unknown');
        $arguments[] = new Argument('no', 'Hell\No');
        $arguments[] = new Argument('what', 'Hell\What', true);
        $arguments[] = new Argument('emails', 'string', false, true);
        $arguments[] = new Argument('stats', 'Hell\Stat', false, true);
        $arguments[] = new Argument('myEnum', 'Hell\MyEnum');

        $constructor = new Constructor('Hell\Yeah', $arguments);
        $definition = new Definition(DefinitionType::data(), 'Hell', 'Yeah', [$constructor], [new Deriving\Equals()]);

        $definition2 = new Definition(
            DefinitionType::data(),
            'Hell',
            'No',
            [new Constructor('Hell\No', [new Argument('noman', 'string')])],
            [new Deriving\ToString()]
        );
        $definition3 = new Definition(
            DefinitionType::data(),
            'Hell',
            'What',
            [new Constructor('Hell\What', [new Argument('whatman', 'int')])],
            [new Deriving\Equals()]
        );
        $definition4 = new Definition(
            DefinitionType::data(),
            'Hell',
            'Stat',
            [new Constructor('Hell\Stat', [new Argument('stat', 'float')])],
            [new Deriving\Equals()]
        );
        $definition5 = new Definition(
            DefinitionType::data(),
            'Hell',
            'MyEnum',
            [new Constructor('Hell\\Value1'), new Constructor('Hell\\Value2')],
            [new Deriving\Enum()]
        );
        $collection = new DefinitionCollection($definition, $definition2, $definition3, $definition4, $definition5);

        $expected = <<<STRING
if (\count(\$this->emails) !== \count(\$yeah->emails)) {
            return false;
        }

        foreach (\$this->emails as \$__i => \$__value) {
            if (\$yeah->emails[\$__i] !== \$__value) {
                return false;
            }
        }

        if (\count(\$this->stats) !== \count(\$yeah->stats)) {
            return false;
        }

        foreach (\$this->stats as \$__i => \$__value) {
            if (! \$yeah->stats[\$__i]->equals(\$__value)) {
                return false;
            }
        }

        return \$this->name === \$yeah->name
            && \$this->whatever === \$yeah->whatever
            && \$this->age->equals(\$yeah->age)
            && \$this->no->toString() === \$yeah->no->toString()
            && ((null === \$this->what && null === \$yeah->what)
                || (null !== \$this->what && null !== \$yeah->what && \$this->what->equals(\$yeah->what))
            )
            && \$this->myEnum->equals(\$yeah->myEnum);
STRING;

        $this->assertSame($expected, buildEqualsBody($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_equals_body_for_composed_objects_without_deriving(): void
    {
        $constructor = new Constructor('Foo\Bar', [new Argument('baz', 'Foo\Baz')]);
        $definition = new Definition(
            DefinitionType::data(),
            'Foo',
            'Bar',
            [
                $constructor,
            ],
            [
                new Deriving\Equals(),
            ]
        );

        $definition2 = new Definition(DefinitionType::data(), 'Foo', 'Baz', [
            new Constructor('Foo\Baz', [
                new Argument('value', 'int'),
            ]),
        ], [
            new Deriving\FromScalar(),
        ]);

        $collection = new DefinitionCollection($definition, $definition2);

        $expected = <<<CODE
return \$this->baz === \$bar->baz;
CODE;

        $this->assertSame($expected, buildEqualsBody($definition, $constructor, $collection, ''));
    }
}
